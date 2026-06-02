<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\Rbac;
use Spatie\Permission\PermissionRegistrar;

it('provisions a workspace and owner on registration', function () {
    $response = $this->post('/register', [
        'workspace' => 'New Co',
        'name' => 'Nina',
        'email' => 'nina@newco.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();

    $tenant = Tenant::where('name', 'New Co')->first();
    expect($tenant)->not->toBeNull()
        ->and($tenant->domains()->count())->toBe(1);

    $owner = User::where('email', 'nina@newco.test')->first();
    expect($owner->tenant_id)->toBe($tenant->id);

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());
    expect($owner->fresh()->hasRole(Rbac::OWNER))->toBeTrue();
});

it('lets an existing user log in', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'email' => 'sam@acme.test',
        'password' => bcrypt('password123'),
    ]);

    $this->post('/login', ['email' => 'sam@acme.test', 'password' => 'password123'])
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

it('rejects bad credentials', function () {
    $this->post('/login', ['email' => 'nobody@acme.test', 'password' => 'wrong'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
