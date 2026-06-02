<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Note;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast to the workspace notifications channel so every connected member
 * gets a live toast when a note is created (Reverb).
 */
class NoteCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Note $note) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('tenant.'.$this->note->tenant_id.'.notifications');
    }

    public function broadcastAs(): string
    {
        return 'NoteCreated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => "New note: {$this->note->title}",
            'note_id' => $this->note->id,
        ];
    }
}
