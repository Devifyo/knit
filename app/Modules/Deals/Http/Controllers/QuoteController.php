<?php

declare(strict_types=1);

namespace App\Modules\Deals\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Quote;
use App\Modules\Deals\Services\PricingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class QuoteController extends Controller
{
    public function __construct(protected PricingService $pricing) {}

    public function index(): InertiaResponse
    {
        $quotes = Quote::with('items')->latest()->limit(100)->get()
            ->map(fn (Quote $q) => [
                'id' => $q->id,
                'number' => $q->number,
                'status' => $q->status,
                'currency' => $q->currency,
                'items' => $q->items->count(),
                'total' => $this->pricing->totals($q)['total_formatted'],
            ])->all();

        return Inertia::render('Quotes/Index', [
            'quotes' => $quotes,
            'products' => Product::where('active', true)->get(['id', 'name', 'unit_price', 'currency']),
        ]);
    }

    public function show(Quote $quote): InertiaResponse
    {
        $quote->load('items');

        return Inertia::render('Quotes/Show', [
            'quote' => [
                'id' => $quote->id,
                'number' => $quote->number,
                'status' => $quote->status,
                'currency' => $quote->currency,
                'tax_rate' => (float) $quote->tax_rate,
                'notes' => $quote->notes,
                'items' => $quote->items->map(fn ($i) => [
                    'id' => $i->id, 'name' => $i->name, 'quantity' => $i->quantity,
                    'unit_price' => $i->unit_price, 'discount_pct' => (float) $i->discount_pct,
                    'line_total' => $this->pricing->format($i->lineTotal(), $quote->currency),
                ]),
            ],
            'totals' => $this->pricing->totals($quote),
            'products' => Product::where('active', true)->get(['id', 'name', 'unit_price', 'currency']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'currency' => ['required', 'string', 'size:3'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'deal_id' => ['nullable', 'exists:deals,id'],
        ]);

        $quote = Quote::create([
            'number' => 'Q-'.now()->format('Ymd').'-'.str_pad((string) (Quote::count() + 1), 4, '0', STR_PAD_LEFT),
            'currency' => strtoupper($data['currency']),
            'tax_rate' => $data['tax_rate'] ?? 0,
            'deal_id' => $data['deal_id'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect("/quotes/{$quote->id}")->with('success', 'Quote created.');
    }

    public function addItem(Request $request, Quote $quote): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $quote->items()->create([
            'name' => $data['name'],
            'quantity' => $data['quantity'],
            'unit_price' => (int) round($data['unit_price'] * 100),
            'discount_pct' => $data['discount_pct'] ?? 0,
            'position' => $quote->items()->count(),
        ]);

        return back()->with('success', 'Item added.');
    }

    public function pdf(Quote $quote): Response
    {
        $quote->load('items');
        $tenant = tenant();

        $pdf = Pdf::loadView('pdf.quote', [
            'quote' => $quote,
            'totals' => $this->pricing->totals($quote),
            'pricing' => $this->pricing,
            'tenantName' => $tenant->name,
            'brandColor' => $tenant->brand_color,
        ]);

        return $pdf->download("{$quote->number}.pdf");
    }
}
