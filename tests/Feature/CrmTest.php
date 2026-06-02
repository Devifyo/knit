<?php

declare(strict_types=1);

use App\Events\DealStageChanged;
use App\Models\Contact;
use App\Models\CustomFieldDefinition;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Stage;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Modules\Leads\Services\LeadConversionService;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function crmWorkspace(string $name, string $email): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => $name, 'owner_name' => $name.' Owner', 'email' => $email, 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('provisions a default pipeline, stages and a contact custom field', function () {
    crmWorkspace('Acme', 'o@acme.test');

    expect(Pipeline::where('is_default', true)->exists())->toBeTrue()
        ->and(Stage::count())->toBe(6)
        ->and(CustomFieldDefinition::where('entity', 'contact')->where('key', 'linkedin')->exists())->toBeTrue();
});

it('surfaces tenant custom fields on the contacts screen', function () {
    [, $owner] = crmWorkspace('Acme', 'o@acme.test');

    $this->actingAs($owner)->get('/contacts')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Contacts/Index', false)
            ->where('customFields.0.key', 'linkedin'));
});

it('converts a lead into a contact and a deal', function () {
    [$tenant, $owner] = crmWorkspace('Acme', 'o@acme.test');
    $lead = Lead::factory()->create(['name' => 'Dana Prospect', 'email' => 'dana@prospect.test', 'assigned_user_id' => $owner->id]);

    $result = app(LeadConversionService::class)->convert($lead);

    expect($result['contact'])->toBeInstanceOf(Contact::class)
        ->and($result['contact']->first_name)->toBe('Dana')
        ->and($result['deal'])->toBeInstanceOf(Deal::class)
        ->and($lead->fresh()->isConverted())->toBeTrue()
        ->and($lead->fresh()->converted_to_contact_id)->toBe($result['contact']->id);
});

it('moves a deal across stages and broadcasts the change', function () {
    Event::fake([DealStageChanged::class]);
    [$tenant, $owner] = crmWorkspace('Acme', 'o@acme.test');

    $pipeline = Pipeline::where('is_default', true)->first();
    $stages = $pipeline->stages()->orderBy('order')->get();
    $deal = Deal::factory()->create([
        'pipeline_id' => $pipeline->id, 'stage_id' => $stages[0]->id, 'owner_id' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->patch("/deals/{$deal->id}/move", ['stage_id' => $stages[2]->id, 'board_order' => 0])
        ->assertRedirect();

    expect($deal->fresh()->stage_id)->toBe($stages[2]->id)
        ->and($deal->fresh()->probability)->toBe($stages[2]->probability);
    Event::assertDispatched(DealStageChanged::class);
});

it('isolates contacts between tenants', function () {
    [$acme, $acmeOwner] = crmWorkspace('Acme', 'o@acme.test');
    Contact::factory()->create(['first_name' => 'Acme', 'last_name' => 'Person', 'owner_id' => $acmeOwner->id]);
    tenancy()->end();

    [$globex, $globexOwner] = crmWorkspace('Globex', 'o@globex.test');
    $globexContact = Contact::factory()->create(['first_name' => 'Globex', 'last_name' => 'Person', 'owner_id' => $globexOwner->id]);
    tenancy()->end();

    // Acme owner sees only Acme's contact; Globex's record 404s.
    $this->actingAs($acmeOwner)->get('/contacts')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Contacts/Index', false)->has('contacts', 1));

    $this->actingAs($acmeOwner)->get("/contacts/{$globexContact->id}")->assertNotFound();
});

it('rejects a duplicate contact email within a workspace', function () {
    [, $owner] = crmWorkspace('Acme', 'o@acme.test');
    Contact::factory()->create(['email' => 'dupe@acme.test', 'owner_id' => $owner->id]);

    $this->actingAs($owner)
        ->post('/contacts', ['first_name' => 'Dup', 'email' => 'dupe@acme.test'])
        ->assertSessionHasErrors('email');
});
