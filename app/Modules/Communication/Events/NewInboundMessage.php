<?php

declare(strict_types=1);

namespace App\Modules\Communication\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Broadcast when a message lands in the shared inbox so open inboxes update live.
 */
class NewInboundMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('tenant.'.$this->message->tenant_id.'.inbox');
    }

    public function broadcastAs(): string
    {
        return 'NewInboundMessage';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->message->conversation_id,
            'preview' => Str::limit(strip_tags($this->message->body), 80),
        ];
    }
}
