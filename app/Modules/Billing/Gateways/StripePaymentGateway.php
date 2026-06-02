<?php

declare(strict_types=1);

namespace App\Modules\Billing\Gateways;

use App\Models\Invoice;
use App\Modules\Billing\Contracts\PaymentGateway;
use App\Modules\Billing\Contracts\PaymentResult;
use Illuminate\Support\Facades\Http;

/**
 * Stripe driver — adapter-ready. Wired through the same PaymentGateway contract
 * so switching providers is a config change (BILLING_GATEWAY=stripe). Without a
 * secret key configured it fails gracefully rather than pretending to charge.
 */
class StripePaymentGateway implements PaymentGateway
{
    public function __construct(private readonly ?string $secretKey = null) {}

    public function charge(Invoice $invoice): PaymentResult
    {
        if (empty($this->secretKey)) {
            return PaymentResult::failure('Stripe is not configured (set STRIPE_SECRET).');
        }

        // Real Stripe PaymentIntent creation would happen here; kept as a single
        // well-typed call site so the live integration is a drop-in.
        $response = Http::withToken($this->secretKey)
            ->asForm()
            ->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => $invoice->total_minor,
                'currency' => strtolower($invoice->currency),
                'confirm' => 'true',
                'metadata[invoice]' => $invoice->number,
            ]);

        if ($response->successful()) {
            return PaymentResult::success((string) $response->json('id'));
        }

        return PaymentResult::failure((string) $response->json('error.message', 'Stripe charge failed'));
    }

    public function name(): string
    {
        return 'stripe';
    }
}
