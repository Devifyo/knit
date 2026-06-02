<?php

declare(strict_types=1);

namespace App\Modules\Billing\Services;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Modules\Billing\Contracts\PaymentGateway;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates the billing lifecycle for the current tenant: trials, plan
 * changes, invoice generation (integer minor units), coupon discounts, and
 * settlement through the configured PaymentGateway. One subscription per tenant.
 */
class BillingService
{
    public function __construct(private readonly PaymentGateway $gateway) {}

    /** The current tenant's subscription, if any. */
    public function current(): ?Subscription
    {
        return Subscription::with('plan')->latest()->first();
    }

    public function startTrial(Plan $plan): Subscription
    {
        $end = $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null;

        return Subscription::create([
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'trial_ends_at' => $end,
            'current_period_start' => now(),
            'current_period_end' => $end,
        ]);
    }

    /**
     * Subscribe (or switch) the tenant to a plan: generate an invoice, settle it
     * through the gateway, and activate the subscription on success.
     */
    public function subscribe(Plan $plan, ?Coupon $coupon = null): Subscription
    {
        return DB::transaction(function () use ($plan, $coupon): Subscription {
            $subscription = $this->current() ?? new Subscription;
            $subscription->plan_id = $plan->id;
            $subscription->save();

            $invoice = $this->generateInvoice($subscription, $coupon);
            $paid = $this->pay($invoice);

            $subscription->update([
                'status' => $paid ? 'active' : 'past_due',
                'canceled_at' => null,
                'current_period_start' => now(),
                'current_period_end' => $plan->interval === 'year' ? now()->addYear() : now()->addMonth(),
            ]);

            if ($paid && $coupon !== null) {
                $coupon->increment('redeemed_count');
            }

            return $subscription->fresh('plan') ?? $subscription;
        });
    }

    public function generateInvoice(Subscription $subscription, ?Coupon $coupon = null): Invoice
    {
        $plan = $subscription->plan ?? Plan::findOrFail($subscription->plan_id);
        $subtotal = $plan->price_minor;
        $discount = 0;
        $couponId = null;
        if ($coupon !== null && $coupon->isRedeemable()) {
            $discount = $coupon->discountFor($subtotal);
            $couponId = $discount > 0 ? $coupon->id : null;
        }
        $total = max(0, $subtotal - $discount);

        $invoice = Invoice::create([
            'subscription_id' => $subscription->id,
            'coupon_id' => $couponId,
            'number' => $this->nextInvoiceNumber(),
            'status' => 'open',
            'currency' => $plan->currency,
            'subtotal_minor' => $subtotal,
            'discount_minor' => $discount,
            'tax_minor' => 0,
            'total_minor' => $total,
            'issued_at' => now(),
        ]);

        $invoice->items()->create([
            'description' => "{$plan->name} plan ({$plan->interval}ly)",
            'quantity' => 1,
            'unit_amount_minor' => $subtotal,
        ]);

        return $invoice;
    }

    /** Settle an invoice through the gateway (zero-total invoices auto-settle). */
    public function pay(Invoice $invoice): bool
    {
        if ($invoice->total_minor === 0) {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
            $invoice->payments()->create([
                'gateway' => $this->gateway->name(), 'reference' => 'FREE',
                'amount_minor' => 0, 'currency' => $invoice->currency, 'status' => 'succeeded', 'paid_at' => now(),
            ]);

            return true;
        }

        $result = $this->gateway->charge($invoice);

        $invoice->payments()->create([
            'gateway' => $this->gateway->name(),
            'reference' => $result->reference,
            'amount_minor' => $invoice->total_minor,
            'currency' => $invoice->currency,
            'status' => $result->success ? 'succeeded' : 'failed',
            'paid_at' => $result->success ? now() : null,
        ]);

        if ($result->success) {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
        }

        return $result->success;
    }

    public function cancel(Subscription $subscription): void
    {
        $subscription->update(['status' => 'canceled', 'canceled_at' => now()]);
    }

    private function nextInvoiceNumber(): string
    {
        $seq = Invoice::count() + 1;

        return 'INV-'.now()->format('Ym').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
