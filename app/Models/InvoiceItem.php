<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['invoice_id', 'description', 'quantity', 'unit_amount_minor'];

    /** @var array<string, string> */
    protected $casts = ['quantity' => 'integer', 'unit_amount_minor' => 'integer'];

    public function lineTotal(): int
    {
        return $this->quantity * $this->unit_amount_minor;
    }

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
