<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Modules\Integrations\Jobs\DeliverWebhookJob;

/**
 * Fans an application event out to every active tenant endpoint subscribed to
 * it. Tenant-guarded and a no-op when no tenant is initialized or no endpoint
 * listens, so it's safe to call from global model events.
 */
class WebhookDispatcher
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string $event, array $payload): void
    {
        if (tenant() === null) {
            return;
        }

        $endpoints = WebhookEndpoint::where('active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($endpoints as $endpoint) {
            $delivery = WebhookDelivery::create([
                'webhook_endpoint_id' => $endpoint->id,
                'event' => $event,
                'payload' => json_encode(['event' => $event, 'data' => $payload], JSON_THROW_ON_ERROR),
            ]);

            DeliverWebhookJob::dispatch(tenant('id'), $delivery->id);
        }
    }
}
