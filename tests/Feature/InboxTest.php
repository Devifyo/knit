<?php

declare(strict_types=1);

use App\Models\ChatMessage;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Modules\Communication\Events\ChatMessageSent;
use App\Modules\Communication\Events\UserMentioned;
use App\Modules\Communication\Services\InboundEmailService;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function commWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Ada', 'email' => 'o@acme.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('threads an inbound email and lands it on the linked contact timeline', function () {
    [$tenant, $owner] = commWorkspace();
    $contact = Contact::factory()->create(['email' => 'jordan@northwind.test', 'owner_id' => $owner->id]);
    $service = app(InboundEmailService::class);

    // First email → new conversation, linked to the contact.
    $first = $service->handle([
        'from_email' => 'jordan@northwind.test', 'subject' => 'Pricing question',
        'body' => 'How much for the enterprise plan?', 'message_id' => 'msg-1',
    ]);
    $conversation = $first->conversation;

    expect($conversation->contact_id)->toBe($contact->id)
        ->and($conversation->messages()->count())->toBe(1);

    // It appears on the contact's unified timeline.
    expect($contact->activities()->where('type', 'email')->exists())->toBeTrue();

    // A reply (In-Reply-To) threads into the SAME conversation, not a new one.
    $second = $service->handle([
        'from_email' => 'jordan@northwind.test', 'subject' => 'Re: Pricing question',
        'body' => 'Also, annual billing?', 'message_id' => 'msg-2', 'in_reply_to' => 'msg-1',
    ]);

    expect($second->conversation_id)->toBe($conversation->id)
        ->and(Conversation::count())->toBe(1)
        ->and($conversation->messages()->count())->toBe(2);
});

it('threads by subject when there is no reply header', function () {
    commWorkspace();
    $service = app(InboundEmailService::class);
    $service->handle(['from_email' => 'a@x.test', 'subject' => 'Support needed', 'body' => 'One', 'message_id' => 'm1']);
    $service->handle(['from_email' => 'a@x.test', 'subject' => 'RE: Support needed', 'body' => 'Two', 'message_id' => 'm2']);

    expect(Conversation::count())->toBe(1);
});

it('accepts inbound email via the public webhook', function () {
    [$tenant] = commWorkspace();
    tenancy()->end();

    $this->postJson("/webhooks/mail/{$tenant->slug}", [
        'from_email' => 'lead@acme-prospect.test', 'subject' => 'Hello', 'body' => 'Inbound via webhook',
    ])->assertCreated()->assertJson(['ok' => true]);

    tenancy()->initialize($tenant);
    expect(Conversation::where('subject', 'Hello')->exists())->toBeTrue();
    tenancy()->end();
});

it('notifies a teammate @mentioned in an internal note', function () {
    Event::fake([UserMentioned::class]);
    [$tenant, $owner] = commWorkspace();
    $mate = User::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Sam Rep']);
    $convo = Conversation::create(['subject' => 'X', 'channel' => 'email', 'status' => 'open']);

    $this->actingAs($owner)->post("/inbox/{$convo->id}/note", ['body' => 'hey @Sam Rep please take this'])
        ->assertRedirect();

    expect($convo->messages()->where('is_internal', true)->count())->toBe(1);
    Event::assertDispatched(UserMentioned::class, fn ($e) => $e->toUserId === $mate->id);
});

it('posts a team chat message and broadcasts it', function () {
    Event::fake([ChatMessageSent::class]);
    [, $owner] = commWorkspace();

    $this->actingAs($owner)->post('/chat', ['body' => 'Hello team'])->assertRedirect();

    expect(ChatMessage::where('body', 'Hello team')->exists())->toBeTrue();
    Event::assertDispatched(ChatMessageSent::class);
});
