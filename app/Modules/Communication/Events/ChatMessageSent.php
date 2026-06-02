<?php

declare(strict_types=1);

namespace App\Modules\Communication\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast a team-chat message on the workspace presence channel.
 */
class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatMessage $message, public string $author) {}

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('tenant.'.$this->message->tenant_id.'.chat');
    }

    public function broadcastAs(): string
    {
        return 'ChatMessageSent';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'body' => $this->message->body,
            'author' => $this->author,
            'user_id' => $this->message->user_id,
        ];
    }
}
