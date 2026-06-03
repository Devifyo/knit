<?php

declare(strict_types=1);

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Form;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Modules\Marketing\Jobs\SendCampaignJob;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function marketingWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Ada', 'email' => 'o@acme.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('creates a form with fully custom fields — nothing is forced', function () {
    [, $owner] = marketingWorkspace();

    $this->actingAs($owner)->post('/forms', [
        'name' => 'Demo request',
        'fields' => [
            ['label' => 'Full name', 'type' => 'text', 'required' => false],
            ['label' => 'Company size', 'type' => 'number', 'required' => true],
            ['label' => 'Demo slot', 'type' => 'datetime', 'required' => false],
        ],
    ])->assertRedirect();

    $form = Form::where('name', 'Demo request')->firstOrFail();

    // Exactly the fields submitted, in order — no Name/Email auto-injected.
    expect(collect($form->fields)->pluck('key')->all())->toBe(['full_name', 'company_size', 'demo_slot'])
        ->and(collect($form->fields)->firstWhere('key', 'company_size'))
        ->toMatchArray(['key' => 'company_size', 'label' => 'Company size', 'type' => 'number', 'required' => true])
        ->and(collect($form->fields)->firstWhere('key', 'demo_slot')['type'])->toBe('datetime');
});

it('requires at least one field', function () {
    [, $owner] = marketingWorkspace();

    $this->actingAs($owner)->post('/forms', ['name' => 'Empty', 'fields' => []])
        ->assertSessionHasErrors('fields');
});

it('edits a form: renames, reorders and changes fields, keeping the slug', function () {
    [, $owner] = marketingWorkspace();
    $form = Form::create([
        'name' => 'Old name', 'slug' => 'keep-me',
        'fields' => [
            ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
            ['key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => false],
        ],
    ]);

    $this->actingAs($owner)->put("/forms/{$form->id}", [
        'name' => 'New name',
        'fields' => [
            // Dropped Name/Email, reordered, added Budget — all allowed now.
            ['label' => 'Budget', 'type' => 'number', 'required' => true],
            ['label' => 'Phone', 'type' => 'tel', 'required' => false],
        ],
    ])->assertRedirect();

    $form->refresh();
    expect($form->name)->toBe('New name')
        ->and($form->slug)->toBe('keep-me')
        ->and(collect($form->fields)->pluck('key')->all())->toBe(['budget', 'phone']);
});

it('rejects a form field with an invalid type', function () {
    [, $owner] = marketingWorkspace();

    $this->actingAs($owner)->post('/forms', [
        'name' => 'Bad form',
        'fields' => [['label' => 'Weird', 'type' => 'rocket', 'required' => false]],
    ])->assertSessionHasErrors('fields.0.type');
});

it('creates a linked lead and enrols it into the nurture sequence on form submit', function () {
    [$tenant, $owner] = marketingWorkspace();

    // A nurture workflow (manual trigger so it only runs via the form enrolment).
    $nurture = Workflow::create(['name' => 'Welcome drip', 'trigger_event' => 'manual', 'enabled' => true]);
    $nurture->steps()->create(['type' => 'send_email', 'config' => ['subject' => 'Welcome', 'body' => 'Hi!'], 'order' => 0]);

    $form = Form::create([
        'name' => 'Contact us', 'slug' => 'contact-us',
        'fields' => [['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true], ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true]],
        'nurture_workflow_id' => $nurture->id,
    ]);
    tenancy()->end();

    // Public submission (no auth).
    $this->get('/forms/contact-us')->assertOk();
    $this->post('/forms/contact-us', ['name' => 'Web Lead', 'email' => 'web@lead.test'])->assertRedirect();

    tenancy()->initialize($tenant);
    $lead = Lead::where('email', 'web@lead.test')->first();
    expect($lead)->not->toBeNull()
        ->and($lead->source)->toContain('Form: Contact us')
        ->and($form->fresh()->submissions_count)->toBe(1)
        // Enrolled into the nurture sequence automatically.
        ->and(WorkflowRun::where('workflow_id', $nurture->id)->where('subject_id', $lead->id)->exists())->toBeTrue();
    tenancy()->end();
});

it('sends a campaign to contacts and records recipients', function () {
    [$tenant, $owner] = marketingWorkspace();
    Contact::factory()->count(3)->create(['owner_id' => $owner->id]); // have emails
    Contact::factory()->create(['owner_id' => $owner->id, 'email' => null]); // skipped

    $campaign = Campaign::create(['name' => 'Blast', 'subject' => 'Hello', 'body' => '<p>Hi</p>', 'cta_label' => 'Go', 'cta_url' => 'https://x.test', 'audience' => 'contacts', 'status' => 'draft']);

    SendCampaignJob::dispatchSync($tenant->id, $campaign->id);

    expect($campaign->fresh()->status)->toBe('sent')
        ->and($campaign->recipients()->count())->toBe(3); // only contacts with email
});

it('tracks opens and clicks for analytics', function () {
    [$tenant, $owner] = marketingWorkspace();
    Contact::factory()->create(['owner_id' => $owner->id, 'email' => 'r@x.test']);
    $campaign = Campaign::create(['name' => 'C', 'subject' => 'S', 'body' => '<p>x</p>', 'cta_url' => 'https://dest.test', 'cta_label' => 'Go', 'audience' => 'contacts', 'status' => 'draft']);
    SendCampaignJob::dispatchSync($tenant->id, $campaign->id);

    $token = $campaign->recipients()->first()->token;
    tenancy()->end();

    $this->get("/track/open/{$token}")->assertOk()->assertHeader('content-type', 'image/gif');
    $this->get("/track/click/{$token}?url=".urlencode('https://dest.test'))->assertRedirect('https://dest.test');

    tenancy()->initialize($tenant);
    $r = $campaign->recipients()->first();
    expect($r->opened_at)->not->toBeNull()
        ->and($r->clicked_at)->not->toBeNull();

    // Analytics surface the counts.
    $this->actingAs($owner)->get("/campaigns/{$campaign->id}")
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Campaigns/Show', false)
            ->where('stats.opens', 1)->where('stats.clicks', 1));
    tenancy()->end();
});

it('splits A/B variants across recipients', function () {
    [$tenant, $owner] = marketingWorkspace();
    Contact::factory()->count(4)->create(['owner_id' => $owner->id]);
    $campaign = Campaign::create(['name' => 'AB', 'subject' => 'A line', 'subject_b' => 'B line', 'body' => '<p>x</p>', 'audience' => 'contacts', 'status' => 'draft']);

    SendCampaignJob::dispatchSync($tenant->id, $campaign->id);

    expect($campaign->recipients()->where('variant', 'A')->count())->toBeGreaterThan(0)
        ->and($campaign->recipients()->where('variant', 'B')->count())->toBeGreaterThan(0);
});
