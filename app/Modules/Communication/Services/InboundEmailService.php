<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Modules\Communication\Events\NewInboundMessage;
use Illuminate\Support\Str;

/**
 * Turns an inbound email payload into a threaded message in the shared inbox,
 * links it to a matching contact, and drops it on that contact's timeline.
 *
 * Threading: reply emails carry an In-Reply-To header → match the original
 * message; otherwise fall back to matching a recent open thread with the same
 * normalized subject. Only then is a new conversation created.
 *
 * Requires an active tenant context (callers resolve the tenant first).
 */
class InboundEmailService
{
    /**
     * @param  array{from_email:string, from_name?:string, subject?:string, body?:string, message_id?:string, in_reply_to?:string}  $payload
     */
    public function handle(array $payload): Message
    {
        $subject = $payload['subject'] ?? '(no subject)';
        $contact = Contact::where('email', $payload['from_email'])->first();
        $conversation = $this->findThread($payload, $subject, $contact?->id)
            ?? Conversation::create([
                'subject' => $subject,
                'channel' => 'email',
                'contact_id' => $contact?->id,
                'status' => 'open',
            ]);

        // Backfill the contact link if the thread predates the match.
        if (! $conversation->contact_id && $contact) {
            $conversation->update(['contact_id' => $contact->id]);
        }

        $message = $conversation->messages()->create([
            'direction' => 'inbound',
            'from_name' => $payload['from_name'] ?? null,
            'from_email' => $payload['from_email'],
            'body' => $payload['body'] ?? '',
            'external_id' => $payload['message_id'] ?? (string) Str::uuid(),
            'in_reply_to' => $payload['in_reply_to'] ?? null,
        ]);

        $conversation->update(['last_message_at' => now(), 'status' => 'open']);

        // Unified timeline: inbound email shows on the linked contact's record.
        if ($contact) {
            Activity::create([
                'type' => 'email',
                'subject_type' => Contact::class,
                'subject_id' => $contact->id,
                'body' => 'Inbound email: '.Str::limit($subject, 80),
            ]);
        }

        event(new NewInboundMessage($message));

        return $message;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function findThread(array $payload, string $subject, ?int $contactId): ?Conversation
    {
        // 1) Reply chain via In-Reply-To header.
        if (! empty($payload['in_reply_to'])) {
            $parent = Message::where('external_id', $payload['in_reply_to'])->first();
            if ($parent) {
                return $parent->conversation;
            }
        }

        // 2) Recent open thread with the same normalized subject.
        $normalized = $this->normalizeSubject($subject);

        return Conversation::where('channel', 'email')
            ->where('updated_at', '>=', now()->subDays(30))
            ->get()
            ->first(fn (Conversation $c) => $this->normalizeSubject($c->subject) === $normalized
                && (! $contactId || $c->contact_id === null || $c->contact_id === $contactId));
    }

    protected function normalizeSubject(string $subject): string
    {
        return Str::lower(trim((string) preg_replace('/^(re|fwd?):\s*/i', '', trim($subject))));
    }
}
