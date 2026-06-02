<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\LoginActivity;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Support\Security\IpMatcher;
use Illuminate\Auth\Events\Login;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function securityWorkspace(string $name = 'Acme', string $email = 'o@acme.test'): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => $name, 'owner_name' => $name.' Owner', 'email' => $email, 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('matches IPs against single addresses and CIDR ranges', function () {
    expect(IpMatcher::matches('127.0.0.1', '127.0.0.1'))->toBeTrue()
        ->and(IpMatcher::matches('10.1.2.3', '10.0.0.0/8'))->toBeTrue()
        ->and(IpMatcher::matches('11.1.2.3', '10.0.0.0/8'))->toBeFalse()
        ->and(IpMatcher::matches('192.168.1.5', '192.168.1.0/24'))->toBeTrue()
        ->and(IpMatcher::matches('192.168.2.5', '192.168.1.0/24'))->toBeFalse();
});

it('blocks requests from IPs outside the allow-list', function () {
    [$tenant, $owner] = securityWorkspace();

    $tenant->update(['allowed_ips' => ['10.0.0.0/8']]); // test client is 127.0.0.1
    $this->actingAs($owner)->get('/dashboard')->assertForbidden();

    $tenant->update(['allowed_ips' => ['127.0.0.1']]);
    $this->actingAs($owner)->get('/dashboard')->assertOk();

    $tenant->update(['allowed_ips' => []]); // empty = allow all
    $this->actingAs($owner)->get('/dashboard')->assertOk();
});

it('forces users to enrol in 2FA when the workspace requires it', function () {
    [$tenant, $owner] = securityWorkspace();
    $tenant->update(['require_2fa' => true]);

    // No confirmed 2FA → funnelled to the security page.
    $this->actingAs($owner)->get('/dashboard')->assertRedirect('/settings/security');
    // The security page itself stays reachable so they can enrol.
    $this->actingAs($owner)->get('/settings/security')->assertOk();

    // Once confirmed, normal access resumes.
    $owner->forceFill(['two_factor_confirmed_at' => now()])->save();
    $this->actingAs($owner)->get('/dashboard')->assertOk();
});

it('updates the workspace security policy and rejects bad IPs', function () {
    [$tenant, $owner] = securityWorkspace();

    // Must include the admin's own IP (127.0.0.1 in tests) to avoid lockout.
    $this->actingAs($owner)->put('/settings/security', [
        'require_2fa' => true, 'allowed_ips' => "127.0.0.1\n10.0.0.0/8",
    ])->assertRedirect();

    $fresh = Tenant::find($tenant->getTenantKey());
    expect($fresh->require_2fa)->toBeTrue()
        ->and($fresh->allowed_ips)->toBe(['127.0.0.1', '10.0.0.0/8']);

    // An invalid IP is rejected — the prior valid allow-list is left untouched.
    $this->actingAs($owner)->put('/settings/security', [
        'require_2fa' => false, 'allowed_ips' => 'not-an-ip',
    ]);
    expect(Tenant::find($tenant->getTenantKey())->allowed_ips)->toBe(['127.0.0.1', '10.0.0.0/8']);
});

it('refuses an allow-list that would lock the admin out', function () {
    [$tenant, $owner] = securityWorkspace();

    // 127.0.0.1 (the test client) is not in this range → must be rejected.
    $this->actingAs($owner)->put('/settings/security', [
        'require_2fa' => false, 'allowed_ips' => '10.0.0.0/8',
    ]);

    expect(Tenant::find($tenant->getTenantKey())->allowed_ips ?? [])->toBe([]);
});

it('records a login activity row on sign-in', function () {
    [, $owner] = securityWorkspace();

    event(new Login('web', $owner, false));

    expect(LoginActivity::where('user_id', $owner->id)->count())->toBe(1)
        ->and(LoginActivity::where('user_id', $owner->id)->first()->tenant_id)->toBe($owner->tenant_id);
});

it('audits changes to a contact and shows them on the audit log', function () {
    config(['audit.console' => true]); // tests run in console context
    [, $owner] = securityWorkspace();
    $this->actingAs($owner);
    $contact = Contact::factory()->create(['owner_id' => $owner->id, 'first_name' => 'Original']);

    $contact->update(['first_name' => 'Changed']);

    expect(Audit::where('auditable_type', Contact::class)->where('auditable_id', $contact->id)
        ->where('event', 'updated')->exists())->toBeTrue();

    $this->actingAs($owner)->get('/settings/audit')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Settings/Audit', false));
});

it('exports a contact as JSON for data portability', function () {
    [, $owner] = securityWorkspace();
    $contact = Contact::factory()->create(['owner_id' => $owner->id, 'email' => 'jo@example.test']);

    $this->actingAs($owner)->get("/contacts/{$contact->id}/export")
        ->assertOk()
        ->assertJsonPath('contact.email', 'jo@example.test')
        ->assertJsonPath('contact.id', $contact->id);
});

it('anonymizes a contact on erasure (right to be forgotten)', function () {
    [, $owner] = securityWorkspace();
    $contact = Contact::factory()->create(['owner_id' => $owner->id, 'first_name' => 'Jo', 'email' => 'jo@example.test', 'phone' => '555']);

    $this->actingAs($owner)->post("/contacts/{$contact->id}/erase")->assertRedirect();

    $contact->refresh();
    expect($contact->first_name)->toBe('Redacted')
        ->and($contact->email)->toBeNull()
        ->and($contact->phone)->toBeNull()
        ->and($contact->isAnonymized())->toBeTrue();
});
