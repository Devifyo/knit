<?php

declare(strict_types=1);

namespace App\Modules\Support\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Ticket $ticket) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('tenant.'.$this->ticket->tenant_id.'.notifications');
    }

    public function broadcastAs(): string
    {
        return 'NoteCreated'; // reuse the toast listener
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['message' => "New {$this->ticket->priority} ticket: {$this->ticket->subject}"];
    }
}
