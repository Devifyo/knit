<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Stage;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Spatie\Permission\PermissionRegistrar;

/** Spin up a tenant + owner and enter its context. */
function qaWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Acme Owner', 'email' => 'o@acme.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('creates an account wrapping a company', function () {
    [, $owner] = qaWorkspace();
    $company = Company::factory()->create(['name' => 'Globex']);

    $this->actingAs($owner)->post('/accounts', [
        'company_id' => $company->id,
        'health_score' => 80,
        'renewal_status' => 'pending',
    ])->assertRedirect();

    expect(Account::where('company_id', $company->id)->where('health_score', 80)->exists())->toBeTrue();
});

it('rejects an account with an invalid renewal status', function () {
    [, $owner] = qaWorkspace();
    $company = Company::factory()->create();

    $this->actingAs($owner)->post('/accounts', [
        'company_id' => $company->id,
        'renewal_status' => 'banana',
    ])->assertSessionHasErrors('renewal_status');
});

it('finds contacts via global search', function () {
    [, $owner] = qaWorkspace();
    Contact::factory()->create(['first_name' => 'Testy', 'last_name' => 'McTest', 'email' => 'testy@x.test']);

    $res = $this->actingAs($owner)->getJson('/search?q=Testy')->assertOk();

    expect(collect($res->json('results'))->pluck('label'))->toContain('Testy McTest');
});

it('returns no results for a blank search query', function () {
    [, $owner] = qaWorkspace();

    $this->actingAs($owner)->getJson('/search?q=')->assertOk()->assertExactJson(['results' => []]);
});

it('creates a deal linked to a contact and inherits its company', function () {
    [, $owner] = qaWorkspace();
    $company = Company::factory()->create();
    $contact = Contact::factory()->create(['company_id' => $company->id]);
    $stage = Stage::query()->firstOrFail();

    $this->actingAs($owner)->post('/deals', [
        'name' => 'Big delivery deal',
        'stage_id' => $stage->id,
        'contact_id' => $contact->id,
    ])->assertRedirect();

    $deal = Deal::where('name', 'Big delivery deal')->firstOrFail();
    expect($deal->contact_id)->toBe($contact->id)
        ->and($deal->company_id)->toBe($company->id);
});
