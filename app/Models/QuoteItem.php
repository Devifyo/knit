<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['quote_id', 'product_id', 'name', 'quantity', 'unit_price', 'discount_pct', 'position'];

    /** @var array<string, string> */
    protected $casts = ['quantity' => 'integer', 'unit_price' => 'integer', 'discount_pct' => 'decimal:2'];

    /** Line subtotal in minor units, after per-line discount. */
    public function lineTotal(): int
    {
        $gross = $this->unit_price * $this->quantity;

        return (int) round($gross * (1 - (float) $this->discount_pct / 100));
    }
}
