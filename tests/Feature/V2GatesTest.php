<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Deal;
use App\Models\Invitation;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use App\Modules\Admin\Services\Rbac;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Modules\Automation\Services\ConditionEvaluator;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function v2Workspace(string $email = 'o@acme.test'): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Ada', 'email' => $email, 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

// ---- Phase 1 gate: invite a second user who joins the same workspace ----

it('invites a teammate who accepts and joins the same workspace with a role', function () {
    [$tenant, $owner] = v2Workspace();

    $this->actingAs($owner)->post('/members/invite', ['email' => 'rep@acme.test', 'role' => 'Manager'])
        ->assertRedirect();

    $invite = Invitation::where('email', 'rep@acme.test')->firstOrFail();
    expect($invite->isPending())->toBeTrue();

    // Invitee (logged out) opens the link and accepts.
    auth()->logout();
    $this->get("/invite/{$invite->token}")->assertOk();
    $this->post("/invite/{$invite->token}", [
        'name' => 'Rey Rep', 'password' => 'password123', 'password_confirmation' => 'password123',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
    $rep = User::where('email', 'rep@acme.test')->firstOrFail();
    expect($rep->tenant_id)->toBe($tenant->id)
        ->and($invite->fresh()->accepted_at)->not->toBeNull();

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());
    expect($rep->fresh()->hasRole(Rbac::MANAGER))->toBeTrue();

    // Two users now in the workspace.
    expect(User::where('tenant_id', $tenant->id)->count())->toBe(2);
});

// ---- Phase 2 gate: deal ↔ products recompute amount ----

it('recomputes the deal amount from its product line items', function () {
    [$tenant, $owner] = v2Workspace();
    $pipeline = Pipeline::where('is_default', true)->first();
    $deal = Deal::factory()->create([
        'pipeline_id' => $pipeline->id, 'stage_id' => $pipeline->stages()->first()->id,
        'owner_id' => $owner->id, 'amount' => 0, 'currency' => 'USD',
    ]);
    $product = Product::factory()->create(['unit_price' => 10000]); // $100.00

    $this->actingAs($owner)->post("/deals/{$deal->id}/products", [
        'product_id' => $product->id, 'quantity' => 2, 'discount_pct' => 10,
    ])->assertRedirect();

    // 2 * 100.00 - 10% = 180.00 => 18000 minor units
    expect($deal->fresh()->amount)->toBe(18000);
});

// ---- Phase 3 gate: accepted quote sets the deal amount ----

it('sets the deal amount when its quote is accepted', function () {
    [$tenant, $owner] = v2Workspace();
    $pipeline = Pipeline::where('is_default', true)->first();
    $deal = Deal::factory()->create([
        'pipeline_id' => $pipeline->id, 'stage_id' => $pipeline->stages()->first()->id, 'owner_id' => $owner->id,
    ]);
    $quote = Quote::create(['deal_id' => $deal->id, 'number' => 'Q-9', 'currency' => 'EUR', 'tax_rate' => 10]);
    $quote->items()->create(['name' => 'Plan', 'quantity' => 1, 'unit_price' => 20000, 'discount_pct' => 0, 'position' => 0]);

    $this->actingAs($owner)->patch("/quotes/{$quote->id}/status", ['status' => 'accepted'])->assertRedirect();

    // 200.00 + 10% tax = 220.00 => 22000
    expect($deal->fresh()->amount)->toBe(22000)
        ->and($deal->fresh()->currency)->toBe('EUR');
});

// ---- Phase 3 gate: relation-aware condition ("no activity logged") ----

it('evaluates a relation-count condition for "no activity logged"', function () {
    [$tenant, $owner] = v2Workspace();
    $lead = Lead::factory()->create(['assigned_user_id' => $owner->id]);
    $evaluator = app(ConditionEvaluator::class);
    $tree = ['operator' => 'and', 'rules' => [['field' => 'activities_count', 'op' => 'equals', 'value' => 0]]];

    expect($evaluator->evaluate($tree, $lead))->toBeTrue();

    Activity::create(['type' => 'call', 'subject_type' => Lead::class, 'subject_id' => $lead->id, 'user_id' => $owner->id]);
    expect($evaluator->evaluate($tree, $lead->fresh()))->toBeFalse();
});

// ---- Phase 3 gate: Test-run executes against the latest record + logs steps ----

it('test-runs a workflow against the latest record and records the run', function () {
    [$tenant, $owner] = v2Workspace();
    $wf = Workflow::create(['name' => 'T', 'trigger_event' => 'lead.created', 'enabled' => true]);
    $wf->steps()->create(['type' => 'create_task', 'config' => ['title' => 'Do it'], 'order' => 0]);
    Lead::factory()->create(['assigned_user_id' => $owner->id]);

    $this->actingAs($owner)->post("/workflows/{$wf->id}/test")->assertRedirect("/workflows/{$wf->id}/runs");

    $run = WorkflowRun::where('workflow_id', $wf->id)->first();
    expect($run)->not->toBeNull()
        ->and($run->status)->toBe('completed');
});
