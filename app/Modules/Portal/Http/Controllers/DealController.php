<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Product;
use App\Models\Quote;
use App\Modules\Deals\Services\PricingService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Read-only view of the customer's own deals. Scoped through the authenticated
 * contact's relationship — a customer can never reach another contact's deals.
 */
class DealController extends Controller
{
    public function __construct(protected PricingService $pricing) {}

    public function index(): Response
    {
        return Inertia::render('Portal/Deals/Index', [
            'deals' => $this->contact()->deals()->with('stage:id,name')->get()->map(fn (Deal $d) => [
                'id' => $d->id,
                'name' => $d->name,
                'amount' => $d->formattedAmount(),
                'stage' => $d->stage?->name,
                'status' => $d->status,
            ]),
        ]);
    }

    public function show(int $deal): Response
    {
        $model = $this->contact()->deals()->with(['stage:id,name', 'products', 'quotes'])->findOrFail($deal);

        return Inertia::render('Portal/Deals/Show', [
            'deal' => [
                'id' => $model->id,
                'name' => $model->name,
                'amount' => $model->formattedAmount(),
                'status' => $model->status,
                'stage' => $model->stage?->name,
                'expected_close_date' => $model->expected_close_date?->toFormattedDateString(),
                'products' => $model->products->map(function (Product $p) use ($model) {
                    $pivot = $p->pivot;

                    return [
                        'name' => $p->name,
                        'quantity' => $pivot->quantity,
                        'unit_price' => $this->pricing->format($pivot->unit_price, $model->currency),
                        'discount_pct' => (float) $pivot->discount_pct,
                    ];
                }),
                'quotes' => $model->quotes->map(fn (Quote $q) => [
                    'id' => $q->id,
                    'number' => $q->number,
                    'status' => $q->status,
                    'total' => $this->pricing->totals($q)['total_formatted'],
                ]),
            ],
        ]);
    }

    private function contact(): Contact
    {
        /** @var Contact $contact */
        $contact = Auth::guard('contact')->user();

        return $contact;
    }
}
