<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['webhook_endpoint_id', 'event', 'payload', 'status_code', 'success', 'error', 'attempts', 'delivered_at'];

    /** @var array<string, string> */
    protected $casts = ['success' => 'boolean', 'status_code' => 'integer', 'attempts' => 'integer', 'delivered_at' => 'datetime'];

    /** @return BelongsTo<WebhookEndpoint, $this> */
    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }
}
