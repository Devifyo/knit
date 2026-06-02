<?php

declare(strict_types=1);

use App\Models\FieldPermission;
use App\Models\Note;
use App\Models\User;
use App\Modules\Admin\Services\FieldPermissionService;
use App\Modules\Admin\Services\Rbac;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Spatie\Permission\PermissionRegistrar;

function provisionAcme(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme',
        'owner_name' => 'Ada',
        'email' => 'owner@acme.test',
        'password' => 'password',
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    $agent = User::create([
        'tenant_id' => $tenant->getTenantKey(),
        'name' => 'Andy Agent',
        'email' => 'agent@acme.test',
        'password' => bcrypt('password'),
    ]);
    $agent->assignRole(Rbac::AGENT);

    return [$tenant, $tenant->getRelation('owner'), $agent];
}

it('allows an owner to view members', function () {
    [, $owner] = provisionAcme();

    $this->actingAs($owner)->get('/members')->assertOk();
});

it('denies a non-privileged role a restricted action', function () {
    [, , $agent] = provisionAcme();

    // Agents lack members.view -> permission middleware blocks them.
    $this->actingAs($agent)->get('/members')->assertForbidden();
});

it('denies an agent from deleting notes but allows an owner', function () {
    [$tenant, $owner, $agent] = provisionAcme();

    tenancy()->initialize($tenant);
    $note = Note::factory()->create(['user_id' => $owner->id]);
    tenancy()->end();

    $this->actingAs($agent)->delete("/notes/{$note->id}")->assertForbidden();
    $this->actingAs($owner)->delete("/notes/{$note->id}")->assertRedirect();
});

it('enforces field-level permissions', function () {
    [$tenant, $owner, $agent] = provisionAcme();

    tenancy()->initialize($tenant);
    FieldPermission::create([
        'role' => Rbac::AGENT,
        'entity' => 'contact',
        'field_key' => 'annual_revenue',
        'can_view' => false,
        'can_edit' => false,
    ]);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    $service = app(FieldPermissionService::class);

    expect($service->canView($agent, 'contact', 'annual_revenue'))->toBeFalse()
        ->and($service->canView($owner, 'contact', 'annual_revenue'))->toBeTrue()
        ->and($service->canView($agent, 'contact', 'first_name'))->toBeTrue();
    tenancy()->end();
});
