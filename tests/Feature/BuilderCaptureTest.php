<?php

declare(strict_types=1);

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Quote;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function builderWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Ada', 'email' => 'o@acme.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('builds a workflow with ordered, configured steps via the builder', function () {
    [, $owner] = builderWorkspace();

    $this->actingAs($owner)->post('/workflows', [
        'name' => 'Custom flow',
        'trigger_event' => 'lead.created',
        'enabled' => true,
        'steps' => [
            ['type' => 'wait', 'config' => ['days' => 2]],
            ['type' => 'send_email', 'config' => ['subject' => 'Hi', 'body' => 'Hello']],
            ['type' => 'create_task', 'config' => ['title' => 'Call']],
        ],
    ])->assertRedirect('/workflows');

    $wf = Workflow::where('name', 'Custom flow')->with('steps')->first();
    expect($wf)->not->toBeNull()
        ->and($wf->steps)->toHaveCount(3)
        ->and($wf->steps[0]->type)->toBe('wait')
        ->and($wf->steps[0]->order)->toBe(0)
        ->and($wf->steps[2]->config['title'])->toBe('Call');
});

it('replaces steps when a workflow is updated', function () {
    [, $owner] = builderWorkspace();
    $wf = Workflow::create(['name' => 'F', 'trigger_event' => 'lead.created', 'enabled' => true]);
    $wf->steps()->create(['type' => 'wait', 'config' => ['days' => 1], 'order' => 0]);

    $this->actingAs($owner)->put("/workflows/{$wf->id}", [
        'name' => 'F2', 'trigger_event' => 'contact.created', 'enabled' => false,
        'steps' => [['type' => 'add_tag', 'config' => ['tag' => 'vip']]],
    ])->assertRedirect('/workflows');

    $wf->refresh()->load('steps');
    expect($wf->name)->toBe('F2')
        ->and($wf->trigger_event)->toBe('contact.created')
        ->and($wf->enabled)->toBeFalse()
        ->and($wf->steps)->toHaveCount(1)
        ->and($wf->steps[0]->type)->toBe('add_tag');
});

it('creates a quote attached to a deal and shows it on the deal page', function () {
    [, $owner] = builderWorkspace();
    $pipeline = Pipeline::where('is_default', true)->first();
    $deal = Deal::factory()->create([
        'pipeline_id' => $pipeline->id, 'stage_id' => $pipeline->stages()->first()->id, 'owner_id' => $owner->id,
    ]);

    $this->actingAs($owner)->post('/quotes', ['deal_id' => $deal->id, 'currency' => 'USD', 'tax_rate' => 5])
        ->assertRedirect();

    $quote = Quote::where('deal_id', $deal->id)->first();
    expect($quote)->not->toBeNull();

    $this->actingAs($owner)->get("/deals/{$deal->id}")
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Deals/Show', false)->has('deal.quotes', 1));
});

it('accepts a public lead capture submission and feeds the automation engine', function () {
    [$tenant, $owner] = builderWorkspace();
    // A workflow listening on lead.created.
    $wf = Workflow::create(['name' => 'Capture follow-up', 'trigger_event' => 'lead.created', 'enabled' => true]);
    $wf->steps()->create(['type' => 'create_task', 'config' => ['title' => 'New web lead'], 'order' => 0]);
    tenancy()->end();

    // Public form renders and accepts a submission (no auth).
    $this->get("/f/{$tenant->slug}")->assertOk();
    $this->post("/f/{$tenant->slug}", ['name' => 'Web Visitor', 'email' => 'visitor@x.test', 'message' => 'Interested!'])
        ->assertRedirect();

    tenancy()->initialize($tenant);
    $lead = Lead::where('email', 'visitor@x.test')->first();
    expect($lead)->not->toBeNull()
        ->and($lead->source)->toBe('Capture form')
        ->and(WorkflowRun::where('subject_id', $lead->id)->where('workflow_id', $wf->id)->exists())->toBeTrue();
    tenancy()->end();
});
