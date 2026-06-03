<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

/** Spin up a tenant + owner and enter its context. */
function portalWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Acme Owner', 'email' => 'o@acme.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

/** Create a portal-ready (activated) contact with a known password. */
function activatedContact(string $email = 'cust@acme.test'): Contact
{
    return Contact::factory()->create([
        'email' => $email,
        'password' => Hash::make('secret123'),
        'portal_enabled' => true,
        'portal_activated_at' => now(),
    ]);
}

it('lets staff invite a contact, who then sets a password and lands on the portal', function () {
    [, $owner] = portalWorkspace();
    $contact = Contact::factory()->create(['email' => 'cust@acme.test']);

    $this->actingAs($owner)->post("/contacts/{$contact->id}/portal-access", ['action' => 'enable'])->assertRedirect();
    $contact->refresh();
    expect($contact->portal_enabled)->toBeTrue()->and($contact->portal_token)->not->toBeNull();

    $this->post("/portal/activate/{$contact->portal_token}", [
        'password' => 'secret123', 'password_confirmation' => 'secret123',
    ])->assertRedirect('/portal');

    $contact->refresh();
    expect($contact->portal_activated_at)->not->toBeNull()
        ->and($contact->portal_token)->toBeNull()
        ->and(Hash::check('secret123', $contact->password))->toBeTrue();
});

it('logs an activated contact in and shows their dashboard', function () {
    portalWorkspace();
    activatedContact();

    $this->post('/portal/login', ['email' => 'cust@acme.test', 'password' => 'secret123'])
        ->assertRedirect('/portal');

    $this->get('/portal')->assertOk()->assertInertia(fn ($p) => $p->component('Portal/Dashboard', false));
});

it('rejects a wrong portal password', function () {
    portalWorkspace();
    activatedContact();

    $this->post('/portal/login', ['email' => 'cust@acme.test', 'password' => 'nope'])
        ->assertSessionHasErrors('email');
});

it('redirects unauthenticated portal visitors to the portal login', function () {
    $this->get('/portal')->assertRedirect('/portal/login');
});

it('a contact can only see their own tickets, never another contact\'s', function () {
    portalWorkspace();
    $alice = activatedContact('alice@acme.test');
    $bob = activatedContact('bob@acme.test');

    $bobTicket = $bob->tickets()->create([
        'number' => 'T-X', 'subject' => "Bob's issue", 'body' => '...', 'channel' => 'portal', 'status' => 'open', 'priority' => 'normal',
    ]);
    $aliceTicket = $alice->tickets()->create([
        'number' => 'T-Y', 'subject' => "Alice's issue", 'body' => '...', 'channel' => 'portal', 'status' => 'open', 'priority' => 'normal',
    ]);

    // Alice sees her own…
    $this->actingAs($alice, 'contact')->get("/portal/tickets/{$aliceTicket->id}")->assertOk();
    // …but never Bob's.
    $this->actingAs($alice, 'contact')->get("/portal/tickets/{$bobTicket->id}")->assertNotFound();
});

it('lets a contact open a ticket from the portal', function () {
    portalWorkspace();
    $alice = activatedContact('alice@acme.test');

    $this->actingAs($alice, 'contact')->post('/portal/tickets', [
        'subject' => 'Need help', 'body' => 'My widget is broken', 'priority' => 'high',
    ])->assertRedirect();

    expect($alice->tickets()->where('subject', 'Need help')->where('channel', 'portal')->exists())->toBeTrue();
});
