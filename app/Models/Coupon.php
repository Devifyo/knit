<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A discount coupon — global catalogue (not tenant-owned). `percent` discounts
 * a percentage; `fixed` discounts a number of minor units.
 */
class Coupon extends Model
{
    /** @var list<string> */
    protected $fillable = ['code', 'type', 'value', 'currency', 'max_redemptions', 'redeemed_count', 'expires_at', 'active'];

    /** @var array<string, string> */
    protected $casts = [
        'value' => 'integer',
        'max_redemptions' => 'integer',
        'redeemed_count' => 'integer',
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function isRedeemable(): bool
    {
        if (! $this->active) {
            return false;
        }
        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return $this->max_redemptions === null || $this->redeemed_count < $this->max_redemptions;
    }

    /** Discount (in minor units) this coupon applies to a subtotal. */
    public function discountFor(int $subtotalMinor): int
    {
        $discount = $this->type === 'percent'
            ? (int) round($subtotalMinor * $this->value / 100)
            : $this->value;

        return min($discount, $subtotalMinor);
    }
}
