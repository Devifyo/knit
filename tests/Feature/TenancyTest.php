<?php

declare(strict_types=1);

use App\Models\Note;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;

/**
 * @return array{0: Tenant, 1: User}
 */
function workspace(string $name, string $email): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => $name,
        'owner_name' => $name.' Owner',
        'email' => $email,
        'password' => 'password',
    ]);

    return [$tenant, $tenant->getRelation('owner')];
}

it('isolates notes between tenants on the index endpoint', function () {
    [$acme, $acmeOwner] = workspace('Acme', 'owner@acme.test');
    [$globex] = workspace('Globex', 'owner@globex.test');

    tenancy()->initialize($acme);
    Note::factory()->create(['user_id' => $acmeOwner->id, 'title' => 'Acme secret']);
    tenancy()->end();

    tenancy()->initialize($globex);
    Note::factory()->create(['user_id' => $globex->users()->first()->id, 'title' => 'Globex secret']);
    tenancy()->end();

    $this->actingAs($acmeOwner)
        ->get('/notes')
        ->assertOk();

    // Assert at the data layer that Acme only ever sees its own row.
    tenancy()->initialize($acme);
    expect(Note::pluck('title')->all())->toBe(['Acme secret']);
    expect(Note::withoutGlobalScopes()->count())->toBe(2);
    tenancy()->end();
});

it('returns 404 for direct access to another tenant\'s record', function () {
    [$acme, $acmeOwner] = workspace('Acme', 'owner@acme.test');
    [$globex] = workspace('Globex', 'owner@globex.test');

    tenancy()->initialize($globex);
    $globexNote = Note::factory()->create(['user_id' => $globex->users()->first()->id]);
    tenancy()->end();

    // Acme owner trying to delete a Globex note -> scoped route binding 404s.
    $this->actingAs($acmeOwner)
        ->delete("/notes/{$globexNote->id}")
        ->assertNotFound();
});

it('resolves the tenant by custom domain (domain wins over the user fallback)', function () {
    [$acme, $acmeOwner] = workspace('Acme', 'owner@acme.test');
    [$globex] = workspace('Globex', 'owner@globex.test');

    $globex->domains()->create(['domain' => 'crm.globex.example']);

    // Authenticated as the Acme owner, but the request host is Globex's domain.
    $this->actingAs($acmeOwner)
        ->get('http://crm.globex.example/current-workspace')
        ->assertOk()
        ->assertJson(['tenant_id' => $globex->id]);
});

it('resolves the tenant by subdomain slug', function () {
    [$acme, $acmeOwner] = workspace('Acme', 'owner@acme.test');

    $this->actingAs($acmeOwner)
        ->get("http://{$acme->slug}.localhost/current-workspace")
        ->assertOk()
        ->assertJson(['tenant_id' => $acme->id]);
});

it('falls back to the authenticated user\'s workspace on the central host', function () {
    [$acme, $acmeOwner] = workspace('Acme', 'owner@acme.test');

    $this->actingAs($acmeOwner)
        ->get('http://localhost/current-workspace')
        ->assertOk()
        ->assertJson(['tenant_id' => $acme->id]);
});
