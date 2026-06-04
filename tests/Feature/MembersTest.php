<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Mail\MemberCredentialsMail;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function membersWorkspace(string $name = 'Acme', string $email = 'o@acme.test'): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => $name, 'owner_name' => $name.' Owner', 'email' => $email, 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('creates a member directly and emails their credentials', function () {
    Mail::fake();
    [$tenant, $owner] = membersWorkspace();

    $this->actingAs($owner)->post('/members', ['name' => 'New Person', 'email' => 'np@acme.test', 'role' => 'Agent'])
        ->assertRedirect();

    $user = User::where('email', 'np@acme.test')->first();
    expect($user)->not->toBeNull();

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());
    expect($user->fresh()->hasRole('Agent'))->toBeTrue();

    Mail::assertSent(MemberCredentialsMail::class, fn ($m) => $m->hasTo('np@acme.test'));
});

it('rejects a duplicate member email', function () {
    [, $owner] = membersWorkspace();

    $this->actingAs($owner)->post('/members', ['name' => 'Dup', 'email' => $owner->email, 'role' => 'Agent'])
        ->assertSessionHasErrors('email');
});

it('updates what a role can do', function () {
    [$tenant, $owner] = membersWorkspace();

    $this->actingAs($owner)->put('/roles/Agent/permissions', ['permissions' => ['leads.view', 'leads.manage']])
        ->assertRedirect();

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());
    $role = Role::where('name', 'Agent')->where('tenant_id', $tenant->getTenantKey())->first();
    expect($role->permissions->pluck('name')->all())->toEqualCanonicalizing(['leads.view', 'leads.manage']);
});

it('refuses to edit the Owner role', function () {
    [, $owner] = membersWorkspace();

    $this->actingAs($owner)->put('/roles/Owner/permissions', ['permissions' => []])
        ->assertForbidden();
});

it('exposes role permissions + grouped catalogue on the members page', function () {
    [, $owner] = membersWorkspace();

    $this->actingAs($owner)->get('/members')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Members/Index', false)
            ->where('canManageRoles', true)
            ->has('permissionGroups')
            ->has('rolePermissions.Owner')
            ->has('rolePermissions.Agent'));
});
