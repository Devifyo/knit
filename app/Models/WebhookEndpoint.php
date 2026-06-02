<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookEndpoint extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['url', 'secret', 'events', 'active', 'created_by'];

    /** @var array<string, string> */
    protected $casts = ['events' => 'array', 'active' => 'boolean'];

    /** @var list<string> */
    protected $hidden = ['secret'];

    /** @return HasMany<WebhookDelivery, $this> */
    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class)->latest();
    }

    public function subscribesTo(string $event): bool
    {
        return $this->active && in_array($event, $this->events ?? [], true);
    }
}
