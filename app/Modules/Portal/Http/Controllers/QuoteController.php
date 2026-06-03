<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Quote;
use App\Modules\Deals\Services\PricingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * The customer's own quotes (those attached to their deals). They can view,
 * download a branded PDF, and accept or decline a quote that's been sent.
 */
class QuoteController extends Controller
{
    public function __construct(protected PricingService $pricing) {}

    public function index(): Response
    {
        return Inertia::render('Portal/Quotes/Index', [
            'quotes' => $this->scope()->with('deal:id,name')->latest()->get()->map(fn (Quote $q) => [
                'id' => $q->id,
                'number' => $q->number,
                'status' => $q->status,
                'deal' => $q->deal?->name,
                'total' => $this->pricing->totals($q)['total_formatted'],
            ]),
        ]);
    }

    public function show(int $quote): Response
    {
        $model = $this->find($quote);

        return Inertia::render('Portal/Quotes/Show', [
            'quote' => [
                'id' => $model->id,
                'number' => $model->number,
                'status' => $model->status,
                'currency' => $model->currency,
                'tax_rate' => (float) $model->tax_rate,
                'notes' => $model->notes,
                'can_respond' => ! in_array($model->status, ['accepted', 'declined'], true),
                'items' => $model->items->map(fn ($i) => [
                    'name' => $i->name,
                    'quantity' => $i->quantity,
                    'unit_price' => $this->pricing->format($i->unit_price, $model->currency),
                    'discount_pct' => (float) $i->discount_pct,
                    'line_total' => $this->pricing->format($i->lineTotal(), $model->currency),
                ]),
            ],
            'totals' => $this->pricing->totals($model),
        ]);
    }

    public function pdf(int $quote): HttpResponse
    {
        $model = $this->find($quote);
        $tenant = tenant();

        return Pdf::loadView('pdf.quote', [
            'quote' => $model,
            'totals' => $this->pricing->totals($model),
            'pricing' => $this->pricing,
            'tenantName' => $tenant?->name,
            'brandColor' => $tenant?->brand_color,
        ])->download("{$model->number}.pdf");
    }

    public function respond(Request $request, int $quote): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', 'in:accept,decline']]);
        $model = $this->find($quote);

        if (in_array($model->status, ['accepted', 'declined'], true)) {
            return back()->with('error', 'This quote has already been responded to.');
        }

        if ($data['decision'] === 'accept') {
            $model->update(['status' => 'accepted']);
            // Accepting syncs the linked deal's amount + currency to the quote.
            if ($model->deal_id) {
                $totals = $this->pricing->totals($model->fresh('items'));
                $model->deal?->forceFill(['amount' => $totals['total'], 'currency' => $model->currency])->save();
            }

            return back()->with('success', 'Quote accepted — thank you!');
        }

        $model->update(['status' => 'declined']);

        return back()->with('success', 'Quote declined.');
    }

    /**
     * Quotes belonging to the current contact's deals.
     *
     * @return Builder<Quote>
     */
    private function scope(): Builder
    {
        $contact = $this->contact();

        return Quote::whereHas('deal', fn (Builder $q) => $q->where('contact_id', $contact->id));
    }

    private function find(int $quote): Quote
    {
        return $this->scope()->with('items')->findOrFail($quote);
    }

    private function contact(): Contact
    {
        /** @var Contact $contact */
        $contact = Auth::guard('contact')->user();

        return $contact;
    }
}
