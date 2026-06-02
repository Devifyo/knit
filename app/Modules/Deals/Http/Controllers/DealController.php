<?php

declare(strict_types=1);

namespace App\Modules\Deals\Http\Controllers;

use App\Events\DealStageChanged;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\Stage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DealController extends Controller
{
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
