<?php

declare(strict_types=1);

namespace App\Modules\Support\Services;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\Ticket;
use App\Modules\Support\Contracts\ChannelAdapter;
use App\Modules\Support\Events\TicketCreated;
use Illuminate\Support\Str;

/**
 * Omnichannel ticket intake: takes a raw payload + the channel adapter that
 * knows how to read it, then creates a ticket — links the customer's contact,
 * starts the SLA timer, auto-assigns an agent, and drops it on the contact's
 * timeline. Requires an active tenant context.
 */
class TicketIntakeService
{
    public function __construct(
        protected SlaService $sla,
        protected AssignmentService $assignment,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function fromChannel(ChannelAdapter $adapter, array $payload, string $priority = 'normal'): Ticket
    {
        $data = $adapter->normalize($payload);
        $contact = $data['from_email'] ? Contact::where('email', $data['from_email'])->first() : null;

        $ticket = Ticket::create([
            'number' => 'T-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'subject' => $data['subject'],
            'body' => $data['body'],
            'contact_id' => $contact?->id,
            'channel' => $adapter->channel(),
            'status' => 'open',
            'priority' => $priority,
            'sla_due_at' => $this->sla->dueAt($priority),
            'assigned_user_id' => $this->assignment->pickAssignee()?->id,
        ]);

        $ticket->replies()->create([
            'user_id' => null, // from the customer
            'body' => $data['body'],
        ]);

        if ($contact) {
            Activity::create([
                'type' => 'system',
                'subject_type' => Contact::class,
                'subject_id' => $contact->id,
                'body' => "Support ticket {$ticket->number} opened: ".Str::limit($data['subject'], 60),
            ]);
        }

        event(new TicketCreated($ticket));

        return $ticket;
    }
}
