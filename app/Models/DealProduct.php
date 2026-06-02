<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Typed pivot for the deal ↔ product line items.
 *
 * @property int $id
 * @property int $quantity
 * @property int $unit_price
 * @property float $discount_pct
 */
class DealProduct extends Pivot
{
    protected $table = 'deal_product';
}
