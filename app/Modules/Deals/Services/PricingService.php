<?php

declare(strict_types=1);

namespace App\Modules\Deals\Services;

use App\Models\Quote;

/**
 * Computes quote totals in integer minor units (no float money). Per-line
 * discounts are applied first, then a quote-level tax rate. Currency-aware.
 */
class PricingService
{
    /**
     * @return array{
     *   subtotal:int, tax:int, total:int, currency:string,
     *   subtotal_formatted:string, tax_formatted:string, total_formatted:string,
     *   tax_rate:float
     * }
     */
    public function totals(Quote $quote): array
    {
        $quote->loadMissing('items');

        $subtotal = $quote->items->sum(fn ($item) => $item->lineTotal());
        $taxRate = (float) $quote->tax_rate;
        $tax = (int) round($subtotal * $taxRate / 100);
        $total = $subtotal + $tax;
        $currency = $quote->currency;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'currency' => $currency,
            'tax_rate' => $taxRate,
            'subtotal_formatted' => $this->format($subtotal, $currency),
            'tax_formatted' => $this->format($tax, $currency),
            'total_formatted' => $this->format($total, $currency),
        ];
    }

    public function format(int $minor, string $currency): string
    {
        $amount = number_format($minor / 100, 2);

        return match (strtoupper($currency)) {
            'USD' => '$'.$amount,
            'EUR' => '€'.$amount,
            'GBP' => '£'.$amount,
            'INR' => '₹'.$amount,
            default => $amount.' '.$currency,
        };
    }
}
