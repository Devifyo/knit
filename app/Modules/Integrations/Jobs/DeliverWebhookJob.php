<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Jobs;

use App\Models\Tenant;
use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

/**
 * Delivers a single webhook payload to its endpoint, signed with the endpoint
 * secret (HMAC-SHA256 over the raw body, sent as `X-Knit-Signature`). Tenant-
 * aware and idempotent-friendly: it records the outcome on the delivery row and
 * retries on transport failure.
 */
class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $tenantId, public int $deliveryId) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (! $tenant) {
            return;
        }
        tenancy()->initialize($tenant);

        try {
            $delivery = WebhookDelivery::with('endpoint')->find($this->deliveryId);
            if (! $delivery || $delivery->success || $delivery->endpoint === null) {
                return;
            }

            $endpoint = $delivery->endpoint;
            $body = $delivery->payload;
            $signature = hash_hmac('sha256', $body, $endpoint->secret);

            $delivery->increment('attempts');

            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Knit-Event' => $delivery->event,
                        'X-Knit-Signature' => 'sha256='.$signature,
                    ])
                    ->withBody($body, 'application/json')
                    ->post($endpoint->url);

                $delivery->update([
                    'status_code' => $response->status(),
                    'success' => $response->successful(),
                    'error' => $response->successful() ? null : 'HTTP '.$response->status(),
                    'delivered_at' => now(),
                ]);

                if (! $response->successful()) {
                    $this->release(30);
                }
            } catch (\Throwable $e) {
                $delivery->update(['success' => false, 'error' => $e->getMessage(), 'delivered_at' => now()]);
                throw $e; // let the queue retry per $tries
            }
        } finally {
            tenancy()->end();
        }
    }
}
