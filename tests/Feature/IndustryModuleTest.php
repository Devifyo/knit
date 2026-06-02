<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\ModuleRecord;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Modules\Industry\Services\Modules;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function moduleWorkspace(string $name = 'Acme', string $email = 'o@acme.test'): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => $name, 'owner_name' => $name.' Owner', 'email' => $email, 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('enables a module per-tenant and surfaces it in the sidebar nav', function () {
    [, $owner] = moduleWorkspace();

    $this->actingAs($owner)->post('/settings/modules/real_estate/toggle')->assertRedirect();

    expect(app(Modules::class)->isEnabled('real_estate'))->toBeTrue();
    $nav = app(Modules::class)->navEntries();
    expect($nav)->toHaveCount(1)
        ->and($nav[0]['href'])->toBe('/m/real_estate/property');
});

it('404s on module records while the module is disabled, 200 once enabled', function () {
    [, $owner] = moduleWorkspace();

    $this->actingAs($owner)->get('/m/real_estate/property')->assertNotFound();

    app(Modules::class)->setEnabled('real_estate', true);
    $this->actingAs($owner)->get('/m/real_estate/property')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Modules/Index', false)->where('entity.key', 'property'));
});

it('404s for an unknown module or entity', function () {
    [, $owner] = moduleWorkspace();
    app(Modules::class)->setEnabled('real_estate', true);

    $this->actingAs($owner)->get('/m/real_estate/unicorn')->assertNotFound();
    $this->actingAs($owner)->get('/m/nope/property')->assertNotFound();
});

it('creates a record validated against the manifest and links a contact', function () {
    [, $owner] = moduleWorkspace();
    app(Modules::class)->setEnabled('real_estate', true);
    $contact = Contact::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($owner)->post('/m/real_estate/property', [
        'address' => '5 Birch Road', 'price' => '425000.50', 'bedrooms' => '3',
        'type' => 'House', 'status' => 'For sale', 'contact_id' => $contact->id,
    ])->assertRedirect();

    $record = ModuleRecord::first();
    expect($record->title)->toBe('5 Birch Road')
        ->and($record->status)->toBe('For sale')
        ->and($record->contact_id)->toBe($contact->id)
        ->and($record->data['price'])->toBe(42500050) // stored as integer minor units
        ->and($record->module_key)->toBe('real_estate');
});

it('rejects records that violate the manifest (required + invalid select)', function () {
    [, $owner] = moduleWorkspace();
    app(Modules::class)->setEnabled('real_estate', true);

    // Missing required address/price, and an invalid status option.
    $this->actingAs($owner)->post('/m/real_estate/property', [
        'status' => 'Demolished',
    ])->assertSessionHasErrors(['address', 'price', 'status']);

    expect(ModuleRecord::count())->toBe(0);
});

it('isolates module records between tenants', function () {
    [, $acmeOwner] = moduleWorkspace('Acme', 'o@acme.test');
    app(Modules::class)->setEnabled('real_estate', true);
    ModuleRecord::create(['module_key' => 'real_estate', 'entity_key' => 'property', 'title' => 'Acme house', 'owner_id' => $acmeOwner->id, 'data' => []]);
    tenancy()->end();

    [, $globexOwner] = moduleWorkspace('Globex', 'o@globex.test');
    app(Modules::class)->setEnabled('real_estate', true);
    tenancy()->end();

    // Globex enabled the module independently but sees none of Acme's records.
    tenancy()->initialize(Tenant::where('name', 'Globex')->first());
    expect(ModuleRecord::count())->toBe(0);
    tenancy()->end();
});

it('keeps module enablement independent per tenant', function () {
    [$acme, $acmeOwner] = moduleWorkspace('Acme', 'o@acme.test');
    app(Modules::class)->setEnabled('recruitment', true);
    tenancy()->end();

    [$globex] = moduleWorkspace('Globex', 'o@globex.test');
    expect(app(Modules::class)->isEnabled('recruitment'))->toBeFalse();
    tenancy()->end();

    tenancy()->initialize($acme);
    expect(app(Modules::class)->isEnabled('recruitment'))->toBeTrue();
    tenancy()->end();
});
