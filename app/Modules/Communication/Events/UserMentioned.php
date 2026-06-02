<?php

declare(strict_types=1);

namespace App\Modules\Communication\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Notify a teammate they were @mentioned in an internal note (live toast).
 */
class UserMentioned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $toUserId, public string $tenantId, public string $message) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('tenant.'.$this->tenantId.'.notifications');
    }

    public function broadcastAs(): string
    {
        return 'UserMentioned';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['to_user_id' => $this->toUserId, 'message' => $this->message];
    }
}
