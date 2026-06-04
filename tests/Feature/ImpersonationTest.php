<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\Rbac;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function impWorkspace(string $name = 'Acme', string $email = 'o@acme.test'): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => $name, 'owner_name' => $name.' Owner', 'email' => $email, 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('lets a permitted member impersonate a contact in the portal', function () {
    [, $owner] = impWorkspace(); // owner has every permission
    $contact = Contact::factory()->create(['email' => 'cust@acme-co.com', 'owner_id' => $owner->id]);

    $this->actingAs($owner)->post("/contacts/{$contact->id}/impersonate")
        ->assertRedirect('/portal');

    $this->assertAuthenticatedAs($contact, 'contact');
    expect(session('impersonator_user_id'))->toBe($owner->id);
});

it('blocks impersonation for members without the permission', function () {
    [$tenant, $owner] = impWorkspace();
    $agent = User::factory()->create(['tenant_id' => $tenant->getTenantKey()]);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());
    $agent->assignRole(Rbac::AGENT); // Agent lacks contacts.impersonate by default
    $contact = Contact::factory()->create(['email' => 'cust@acme-co.com', 'owner_id' => $owner->id]);

    $this->actingAs($agent)->post("/contacts/{$contact->id}/impersonate")
        ->assertForbidden();

    $this->assertGuest('contact');
});

it('exits impersonation back to the contact page', function () {
    [, $owner] = impWorkspace();
    $contact = Contact::factory()->create(['email' => 'cust@acme-co.com', 'owner_id' => $owner->id]);

    $this->actingAs($owner)->post("/contacts/{$contact->id}/impersonate");

    $this->post('/portal/stop-impersonating')
        ->assertRedirect("/contacts/{$contact->id}");

    $this->assertGuest('contact');
    expect(session('impersonator_user_id'))->toBeNull();
});
