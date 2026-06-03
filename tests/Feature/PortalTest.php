<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Quote;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Modules\Leads\Services\LeadConversionService;
use App\Modules\Portal\Mail\PortalAccessMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

it('shows a contact only their own quotes and lets them accept one', function () {
    portalWorkspace();
    $alice = activatedContact('alice@acme.test');
    $bob = activatedContact('bob@acme.test');

    $pipeline = Pipeline::where('is_default', true)->first();
    $stage = $pipeline->stages()->first();

    $aliceDeal = Deal::factory()->create(['contact_id' => $alice->id, 'pipeline_id' => $pipeline->id, 'stage_id' => $stage->id]);
    $bobDeal = Deal::factory()->create(['contact_id' => $bob->id, 'pipeline_id' => $pipeline->id, 'stage_id' => $stage->id]);

    $aliceQuote = Quote::create(['deal_id' => $aliceDeal->id, 'number' => 'Q-A', 'status' => 'sent', 'currency' => 'USD', 'tax_rate' => 0]);
    $aliceQuote->items()->create(['name' => 'Service', 'quantity' => 2, 'unit_price' => 5000, 'discount_pct' => 0]);
    $bobQuote = Quote::create(['deal_id' => $bobDeal->id, 'number' => 'Q-B', 'status' => 'sent', 'currency' => 'USD', 'tax_rate' => 0]);

    // Alice sees her quote, not Bob's.
    $this->actingAs($alice, 'contact')->get("/portal/quotes/{$aliceQuote->id}")->assertOk();
    $this->actingAs($alice, 'contact')->get("/portal/quotes/{$bobQuote->id}")->assertNotFound();

    // Accepting syncs the deal amount to the quote total (2 × $50.00 = $100.00 → 10000 minor).
    $this->actingAs($alice, 'contact')->post("/portal/quotes/{$aliceQuote->id}/respond", ['decision' => 'accept'])->assertRedirect();
    expect($aliceQuote->fresh()->status)->toBe('accepted')
        ->and($aliceDeal->fresh()->amount)->toBe(10000);
});

it('emails the activation link when staff invite a contact', function () {
    Mail::fake();
    [, $owner] = portalWorkspace();
    $contact = Contact::factory()->create(['email' => 'cust@acme.test']);

    $this->actingAs($owner)->post("/contacts/{$contact->id}/portal-access", ['action' => 'enable'])->assertRedirect();

    Mail::assertSent(PortalAccessMail::class, fn (PortalAccessMail $m) => $m->hasTo('cust@acme.test') && $m->reset === false);
});

it('emails a reset link via self-service forgot password', function () {
    Mail::fake();
    portalWorkspace();
    $contact = activatedContact('cust@acme.test');

    $this->post('/portal/forgot-password', ['email' => 'cust@acme.test'])->assertRedirect();

    expect($contact->fresh()->portal_token)->not->toBeNull();
    Mail::assertSent(PortalAccessMail::class, fn (PortalAccessMail $m) => $m->hasTo('cust@acme.test') && $m->reset === true);
});

it('does not reveal whether an email exists on forgot password', function () {
    Mail::fake();
    portalWorkspace();

    $this->post('/portal/forgot-password', ['email' => 'nobody@nowhere.test'])->assertRedirect();

    Mail::assertNothingSent();
});

it('shows a contact only their own projects', function () {
    [, $owner] = portalWorkspace();
    $alice = activatedContact('alice@acme.test');
    $bob = activatedContact('bob@acme.test');

    $aliceProject = $alice->projects()->create(['name' => 'Alice delivery', 'status' => 'active', 'owner_id' => $owner->id]);
    $bobProject = $bob->projects()->create(['name' => 'Bob delivery', 'status' => 'active', 'owner_id' => $owner->id]);

    $this->actingAs($alice, 'contact')->get("/portal/projects/{$aliceProject->id}")->assertOk();
    $this->actingAs($alice, 'contact')->get("/portal/projects/{$bobProject->id}")->assertNotFound();
});

it('auto-invites a converted lead to the portal', function () {
    Mail::fake();
    portalWorkspace();
    $lead = Lead::factory()->create(['name' => 'Dana Prospect', 'email' => 'dana@prospect.test']);

    $result = app(LeadConversionService::class)->convert($lead);
    $contact = $result['contact']->fresh();

    expect($contact->portal_enabled)->toBeTrue()
        ->and($contact->portal_token)->not->toBeNull();
    Mail::assertSent(PortalAccessMail::class, fn (PortalAccessMail $m) => $m->hasTo('dana@prospect.test'));
});

it('lets a contact open a ticket from the portal', function () {
    portalWorkspace();
    $alice = activatedContact('alice@acme.test');

    $this->actingAs($alice, 'contact')->post('/portal/tickets', [
        'subject' => 'Need help', 'body' => 'My widget is broken', 'priority' => 'high',
    ])->assertRedirect();

    expect($alice->tickets()->where('subject', 'Need help')->where('channel', 'portal')->exists())->toBeTrue();
});
