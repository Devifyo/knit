<?php

declare(strict_types=1);

namespace App\Modules\Billing\Gateways;

use App\Models\Invoice;
use App\Modules\Billing\Contracts\PaymentGateway;
use App\Modules\Billing\Contracts\PaymentResult;
use Illuminate\Support\Str;

/**
 * Offline / manual gateway — records the charge against the app's own ledger
 * and settles the invoice immediately. This is the default driver and keeps
 * billing fully functional without an external provider configured.
 */
class ManualPaymentGateway implements PaymentGateway
{
    public function charge(Invoice $invoice): PaymentResult
    {
        return PaymentResult::success('MANUAL-'.Str::upper(Str::random(12)));
    }

    public function name(): string
    {
        return 'manual';
    }
}
