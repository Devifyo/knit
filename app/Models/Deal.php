<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Database\Factories\DealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Deal extends Model implements AuditableContract, TenantOwned
{
    /** @use HasFactory<DealFactory> */
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'name', 'pipeline_id', 'stage_id', 'amount', 'currency', 'probability',
        'expected_close_date', 'contact_id', 'company_id', 'owner_id', 'status',
        'board_order', 'custom_fields',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'amount' => 'integer',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'custom_fields' => 'array',
    ];

    /** Formatted money from minor units. */
    public function formattedAmount(): string
    {
        return number_format($this->amount / 100, 0).' '.$this->currency;
    }

    /** @return BelongsTo<Pipeline, $this> */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    /** @return BelongsTo<Stage, $this> */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    /** @return BelongsTo<Contact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return MorphMany<Activity, $this> */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')->latest();
    }

    /** @return HasMany<Quote, $this> */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class)->latest();
    }

    /** @return BelongsToMany<Product, $this, DealProduct, 'pivot'> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->using(DealProduct::class)
            ->withPivot(['id', 'quantity', 'unit_price', 'discount_pct'])
            ->withTimestamps();
    }

    /**
     * Recalculate this deal's amount (minor units) from its product line items,
     * applying per-line discounts. Called whenever products change.
     */
    public function recalculateAmountFromProducts(): void
    {
        $total = $this->products->sum(function (Product $p) {
            /** @var DealProduct $pivot */
            $pivot = $p->pivot;
            $gross = $pivot->unit_price * $pivot->quantity;

            return (int) round($gross * (1 - $pivot->discount_pct / 100));
        });

        $this->forceFill(['amount' => $total])->save();
    }
}
