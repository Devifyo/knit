<?php

declare(strict_types=1);

namespace App\Modules\Deals\Http\Controllers;

use App\Events\DealStageChanged;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealProduct;
use App\Models\Pipeline;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Stage;
use App\Modules\Deals\Services\PricingService;
use App\Services\AI\GeminiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DealController extends Controller
{
    public function __construct(protected PricingService $pricing) {}

    public function show(Deal $deal): Response
    {
        $deal->load(['contact:id,first_name,last_name', 'company:id,name', 'owner:id,name', 'stage:id,name', 'quotes', 'products', 'activities.user:id,name']);

        return Inertia::render('Deals/Show', [
            'deal' => [
                'id' => $deal->id,
                'name' => $deal->name,
                'amount' => $deal->formattedAmount(),
                'currency' => $deal->currency,
                'status' => $deal->status,
                'stage' => $deal->stage?->name,
                'probability' => $deal->probability,
                'expected_close_date' => $deal->expected_close_date?->toFormattedDateString(),
                'contact' => $deal->contact?->name,
                'contact_id' => $deal->contact_id,
                'company' => $deal->company?->name,
                'owner' => $deal->owner?->name,
                'products' => $deal->products->map(function (Product $p) use ($deal) {
                    /** @var DealProduct $pivot */
                    $pivot = $p->pivot;

                    return [
                        'pivot_id' => $pivot->id,
                        'name' => $p->name,
                        'quantity' => $pivot->quantity,
                        'unit_price' => $this->pricing->format($pivot->unit_price, $deal->currency),
                        'discount_pct' => $pivot->discount_pct,
                    ];
                }),
                'quotes' => $deal->quotes->map(fn (Quote $q) => [
                    'id' => $q->id, 'number' => $q->number, 'status' => $q->status,
                    'total' => $this->pricing->totals($q)['total_formatted'],
                ]),
                'activities' => $deal->activities->map(fn ($a) => [
                    'id' => $a->id, 'type' => $a->type, 'body' => $a->body,
                    'author' => $a->user?->name, 'at' => $a->created_at?->diffForHumans(),
                ]),
            ],
            'catalog' => Product::where('active', true)->get(['id', 'name', 'unit_price', 'currency']),
        ]);
    }

    public function addProduct(Request $request, Deal $deal): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'discount_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $deal->products()->attach($product->id, [
            'tenant_id' => tenant('id'),
            'quantity' => $data['quantity'],
            'unit_price' => $product->unit_price, // snapshot catalog price
            'discount_pct' => $data['discount_pct'] ?? 0,
        ]);
        $deal->load('products')->recalculateAmountFromProducts();

        return back()->with('success', 'Product added — deal amount updated.');
    }

    public function removeProduct(Deal $deal, int $pivotId): RedirectResponse
    {
        $deal->products()->wherePivot('id', $pivotId)->detach();
        $deal->load('products')->recalculateAmountFromProducts();

        return back()->with('success', 'Product removed.');
    }

    public function index(Request $request): Response
    {
        $pipeline = Pipeline::with('stages')
            ->where('type', 'deal')
            ->when($request->filled('pipeline'), fn ($q) => $q->where('id', $request->integer('pipeline')))
            ->orderByDesc('is_default')
            ->first();

        $columns = [];
        if ($pipeline) {
            $deals = Deal::with(['contact:id,first_name,last_name', 'company:id,name', 'owner:id,name'])
                ->where('pipeline_id', $pipeline->id)
                ->orderBy('board_order')
                ->get();

            foreach ($pipeline->stages as $stage) {
                $columns[] = [
                    'id' => $stage->id,
                    'title' => $stage->name,
                    'probability' => $stage->probability,
                    'cards' => $deals->where('stage_id', $stage->id)->values()->map(fn (Deal $d) => [
                        'id' => $d->id,
                        'title' => $d->name,
                        'amount' => $d->formattedAmount(),
                        'company' => $d->company?->name,
                        'contact' => $d->contact?->name,
                        'owner' => $d->owner?->name,
                    ])->all(),
                ];
            }
        }

        return Inertia::render('Deals/Index', [
            'pipeline' => $pipeline ? ['id' => $pipeline->id, 'name' => $pipeline->name] : null,
            'pipelines' => Pipeline::where('type', 'deal')->get(['id', 'name']),
            'columns' => $columns,
        ]);
    }

    /** AI insight: next-best action + deal risk (flashed to the view). */
    public function insight(Deal $deal, GeminiService $ai): RedirectResponse
    {
        return back()->with('ai', [
            'next' => $ai->recommendNextAction($deal),
            'risk' => $ai->predictDealRisk($deal),
        ]);
    }

    public function move(Request $request, Deal $deal): RedirectResponse
    {
        $data = $request->validate([
            'stage_id' => ['required', 'integer'],
            'board_order' => ['nullable', 'integer'],
        ]);

        // Validate the target stage is in the same workspace + pipeline (scoped).
        $stage = Stage::where('id', $data['stage_id'])->where('pipeline_id', $deal->pipeline_id)->firstOrFail();

        $from = $deal->stage_id;
        $deal->forceFill([
            'stage_id' => $stage->id,
            'board_order' => $data['board_order'] ?? 0,
            'probability' => $stage->probability,
            'status' => match ($stage->type) {
                'won' => 'won', 'lost' => 'lost', default => 'open'
            },
        ])->save();

        if ($from !== $stage->id) {
            event(new DealStageChanged($deal, $from, $stage->id));
        }

        return back()->with('success', 'Deal moved.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'stage_id' => ['required', 'integer', 'exists:stages,id'],
        ]);

        $stage = Stage::findOrFail($data['stage_id']);

        Deal::create([
            'name' => $data['name'],
            'amount' => (int) round(($data['amount'] ?? 0) * 100),
            'pipeline_id' => $stage->pipeline_id,
            'stage_id' => $stage->id,
            'probability' => $stage->probability,
            'owner_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Deal created.');
    }
}
