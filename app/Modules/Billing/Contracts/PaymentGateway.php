<?php

declare(strict_types=1);

namespace App\Modules\Billing\Contracts;

use App\Models\Invoice;

/**
 * Payment provider abstraction. The bound implementation is selected by
 * config('services.billing.gateway'); a Stripe/Razorpay driver drops in here
 * without touching the billing flow.
 */
interface PaymentGateway
{
    public function charge(Invoice $invoice): PaymentResult;

    public function name(): string;
}
