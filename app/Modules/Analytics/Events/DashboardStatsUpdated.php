<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a workspace metric changes (e.g. a deal closes) so open
 * dashboards refresh their widgets live. See docs/ARCHITECTURE.md §Real-time.
 */
class DashboardStatsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $tenantId, public string $reason = 'updated') {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('tenant.'.$this->tenantId.'.dashboard');
    }

    public function broadcastAs(): string
    {
        return 'StatsUpdated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['reason' => $this->reason];
    }
}
