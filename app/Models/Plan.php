<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A subscription plan — global catalogue, shared by every tenant (not
 * tenant-owned). Money is integer minor units + currency.
 */
class Plan extends Model
{
    /** @var list<string> */
    protected $fillable = ['key', 'name', 'price_minor', 'currency', 'interval', 'trial_days', 'seats', 'features', 'sort', 'is_active'];

    /** @var array<string, string> */
    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_minor' => 'integer',
        'trial_days' => 'integer',
        'seats' => 'integer',
    ];

    public function feature(string $key, mixed $default = null): mixed
    {
        return $this->features[$key] ?? $default;
    }

    public function isFree(): bool
    {
        return $this->price_minor === 0;
    }
}
