<?php

declare(strict_types=1);

namespace App\Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Billing\Services\Entitlements;
use App\Modules\Deals\Services\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(
        private readonly BillingService $billing,
        private readonly Entitlements $entitlements,
        private readonly PricingService $money,
    ) {}

    public function index(): Response
    {
        $subscription = $this->billing->current();
        $plan = $this->entitlements->plan();

        return Inertia::render('Settings/Billing', [
            'subscription' => $subscription ? [
                'status' => $subscription->status,
                'plan' => $subscription->plan?->name,
                'on_trial' => $subscription->onTrial(),
                'trial_ends_at' => $subscription->trial_ends_at?->toFormattedDateString(),
                'renews_at' => $subscription->current_period_end?->toFormattedDateString(),
                'canceled' => $subscription->status === 'canceled',
            ] : null,
            'usage' => [
                'seats_used' => $this->entitlements->seatsUsed(),
                'seat_limit' => $this->entitlements->seatLimit(),
                'plan' => $plan->name,
            ],
            'plans' => Plan::where('is_active', true)->orderBy('sort')->get()->map(fn (Plan $p): array => [
                'key' => $p->key,
                'name' => $p->name,
                'price' => $this->money->format($p->price_minor, $p->currency),
                'price_minor' => $p->price_minor,
                'interval' => $p->interval,
                'seats' => $p->seats,
                'features' => $p->features ?? [],
                'current' => $subscription?->plan_id === $p->id && $subscription->isActive(),
            ]),
            'invoices' => Invoice::latest()->get()->map(fn (Invoice $i): array => [
                'id' => $i->id,
                'number' => $i->number,
                'status' => $i->status,
                'total' => $this->money->format($i->total_minor, $i->currency),
                'issued_at' => $i->issued_at?->toFormattedDateString(),
            ]),
        ]);
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', 'exists:plans,key'],
            'coupon' => ['nullable', 'string', 'max:64'],
        ]);

        $plan = Plan::where('key', $data['plan'])->where('is_active', true)->firstOrFail();

        $coupon = null;
        if (! empty($data['coupon'])) {
            $coupon = Coupon::where('code', $data['coupon'])->first();
            if ($coupon === null || ! $coupon->isRedeemable()) {
                return back()->withErrors(['coupon' => 'That coupon is invalid or expired.']);
            }
        }

        $subscription = $this->billing->subscribe($plan, $coupon);

        return $subscription->isActive()
            ? back()->with('success', "You're now on the {$plan->name} plan.")
            : back()->withErrors(['plan' => 'Payment could not be completed. Please try again.']);
    }

    public function cancel(): RedirectResponse
    {
        $subscription = $this->billing->current();
        if ($subscription !== null && $subscription->isActive()) {
            $this->billing->cancel($subscription);
        }

        return back()->with('success', 'Subscription canceled.');
    }
}
