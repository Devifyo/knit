<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Modules\Support\Channels\EmailChannelAdapter;
use App\Modules\Support\Events\TicketEscalated;
use App\Modules\Support\Services\EscalationService;
use App\Modules\Support\Services\TicketIntakeService;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function supportWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Ada', 'email' => 'o@acme.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('creates a routed, SLA-timed ticket from an inbound email and links the contact', function () {
    [$tenant, $owner] = supportWorkspace();
    $contact = Contact::factory()->create(['email' => 'pat@customer.test', 'owner_id' => $owner->id]);

    $ticket = app(TicketIntakeService::class)->fromChannel(new EmailChannelAdapter, [
        'from_email' => 'pat@customer.test', 'subject' => 'Login broken', 'body' => 'Help!',
    ], 'high');

    expect($ticket->contact_id)->toBe($contact->id)          // linked to the customer
        ->and($ticket->assigned_user_id)->not->toBeNull()    // auto-routed
        ->and($ticket->sla_due_at)->not->toBeNull()          // SLA timer started
        ->and($ticket->channel)->toBe('email');

    // High priority → 120-minute first-response SLA.
    expect($ticket->sla_due_at->diffInMinutes(now()))->toBeLessThanOrEqual(120);

    // Appears on the contact's timeline.
    expect($contact->activities()->where('type', 'system')->exists())->toBeTrue();
});

it('escalates a ticket that breached its SLA', function () {
    Event::fake([TicketEscalated::class]);
    [$tenant] = supportWorkspace();

    $ticket = Ticket::create([
        'number' => 'T-1', 'subject' => 'Down', 'channel' => 'email', 'status' => 'open',
        'priority' => 'normal', 'sla_due_at' => now()->subHour(), // already overdue, no response
    ]);

    $count = app(EscalationService::class)->escalateBreached();

    expect($count)->toBe(1)
        ->and($ticket->fresh()->escalated_at)->not->toBeNull()
        ->and($ticket->fresh()->priority)->toBe('high'); // bumped normal → high
    Event::assertDispatched(TicketEscalated::class);

    // Idempotent — a second pass does not re-escalate.
    expect(app(EscalationService::class)->escalateBreached())->toBe(0);
});

it('runs the scheduled SLA command across workspaces', function () {
    [$tenant] = supportWorkspace();
    Ticket::create([
        'number' => 'T-2', 'subject' => 'Urgent', 'channel' => 'email', 'status' => 'open',
        'priority' => 'high', 'sla_due_at' => now()->subMinutes(5),
    ]);
    tenancy()->end();

    $this->artisan('tickets:check-sla')->assertSuccessful();

    tenancy()->initialize($tenant);
    expect(Ticket::where('number', 'T-2')->first()->escalated_at)->not->toBeNull();
    tenancy()->end();
});

it('stops the SLA clock on the first public agent reply', function () {
    [$tenant, $owner] = supportWorkspace();
    $ticket = Ticket::create(['number' => 'T-3', 'subject' => 'Q', 'channel' => 'email', 'status' => 'open', 'priority' => 'normal', 'sla_due_at' => now()->addHours(8)]);

    $this->actingAs($owner)->post("/tickets/{$ticket->id}/reply", ['body' => 'Hi, looking into it'])->assertRedirect();

    expect($ticket->fresh()->first_responded_at)->not->toBeNull()
        ->and($ticket->fresh()->status)->toBe('pending');
});

it('accepts a support email via the public webhook', function () {
    [$tenant] = supportWorkspace();
    tenancy()->end();

    $this->postJson("/webhooks/support/{$tenant->slug}", [
        'from_email' => 'new@cust.test', 'subject' => 'Billing question', 'priority' => 'normal',
    ])->assertCreated()->assertJson(['ok' => true]);

    tenancy()->initialize($tenant);
    expect(Ticket::where('subject', 'Billing question')->exists())->toBeTrue();
    tenancy()->end();
});

it('answers via the public help portal chatbot (graceful fallback)', function () {
    [$tenant] = supportWorkspace();
    tenancy()->end();

    $this->get("/help/{$tenant->slug}")->assertOk();
    $this->post("/help/{$tenant->slug}/ask", ['question' => 'How do I reset my password?'])
        ->assertRedirect()
        ->assertSessionHas('answer');
});
