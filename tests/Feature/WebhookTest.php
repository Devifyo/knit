<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function webhookWorkspace(string $name = 'Acme', string $email = 'o@acme.test'): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => $name, 'owner_name' => $name.' Owner', 'email' => $email, 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

function makeEndpoint(string $secret = 'whsec_test', array $events = ['contact.created']): WebhookEndpoint
{
    return WebhookEndpoint::create([
        'url' => 'https://hooks.example.com/in', 'secret' => $secret, 'events' => $events,
    ]);
}

it('delivers a signed webhook when a subscribed event fires', function () {
    Http::fake(['*' => Http::response('', 200)]);
    [, $owner] = webhookWorkspace();
    $endpoint = makeEndpoint('whsec_abc123');

    // Creating a contact fires contact.created → dispatcher → queued delivery.
    Contact::factory()->create(['owner_id' => $owner->id]);

    $delivery = WebhookDelivery::where('event', 'contact.created')->first();
    expect($delivery)->not->toBeNull()
        ->and($delivery->success)->toBeTrue()
        ->and($delivery->status_code)->toBe(200);

    $expected = 'sha256='.hash_hmac('sha256', $delivery->payload, 'whsec_abc123');
    Http::assertSent(fn ($request) => $request->url() === 'https://hooks.example.com/in'
        && $request->header('X-Knit-Signature')[0] === $expected
        && $request->header('X-Knit-Event')[0] === 'contact.created');
});

it('records a failed delivery when the endpoint errors', function () {
    Http::fake(['*' => Http::response('nope', 500)]);
    [, $owner] = webhookWorkspace();
    makeEndpoint();

    Contact::factory()->create(['owner_id' => $owner->id]);

    $delivery = WebhookDelivery::where('event', 'contact.created')->first();
    expect($delivery->success)->toBeFalse()
        ->and($delivery->status_code)->toBe(500);
});

it('does not fire for events the endpoint is not subscribed to', function () {
    Http::fake();
    [, $owner] = webhookWorkspace();
    makeEndpoint('whsec_x', ['deal.created']); // not contact.created

    Contact::factory()->create(['owner_id' => $owner->id]);

    expect(WebhookDelivery::count())->toBe(0);
    Http::assertNothingSent();
});

it('registers an endpoint through the settings screen', function () {
    [, $owner] = webhookWorkspace();

    $this->actingAs($owner)->post('/settings/webhooks', [
        'url' => 'https://example.com/hook', 'events' => ['contact.created', 'deal.created'],
    ])->assertRedirect();

    $endpoint = WebhookEndpoint::first();
    expect($endpoint->url)->toBe('https://example.com/hook')
        ->and($endpoint->events)->toBe(['contact.created', 'deal.created'])
        ->and($endpoint->secret)->toStartWith('whsec_');
});

it('isolates webhook endpoints between tenants', function () {
    Http::fake();
    [$acme, $acmeOwner] = webhookWorkspace('Acme', 'o@acme.test');
    makeEndpoint('whsec_acme', ['contact.created']);
    tenancy()->end();

    [, $globexOwner] = webhookWorkspace('Globex', 'o@globex.test');
    makeEndpoint('whsec_globex', ['contact.created']);
    // Globex contact only reaches Globex's single endpoint.
    Contact::factory()->create(['owner_id' => $globexOwner->id]);
    tenancy()->end();

    tenancy()->initialize($acme);
    expect(WebhookEndpoint::count())->toBe(1)
        ->and(WebhookDelivery::count())->toBe(0); // Globex's delivery is not visible here
    tenancy()->end();
});
