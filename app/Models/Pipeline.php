<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['name', 'type', 'is_default'];

    /** @var array<string, string> */
    protected $casts = ['is_default' => 'boolean'];

    /** @return HasMany<Stage, $this> */
    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class)->orderBy('order');
    }

    /** @return HasMany<Deal, $this> */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}
