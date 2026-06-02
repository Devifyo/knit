<?php

declare(strict_types=1);

use App\Models\Lead;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function autoWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Ada', 'email' => 'o@acme.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

function followUpWorkflow(): Workflow
{
    $wf = Workflow::create(['name' => 'New lead follow-up', 'trigger_event' => 'lead.created', 'enabled' => true]);
    $steps = [
        ['type' => 'wait', 'config' => ['days' => 1]],
        ['type' => 'send_email', 'config' => ['to_field' => 'email', 'subject' => 'Hi', 'body' => 'Following up.']],
        ['type' => 'condition', 'config' => ['condition' => ['operator' => 'and', 'rules' => [['field' => 'status', 'op' => 'equals', 'value' => 'new']]]]],
        ['type' => 'create_task', 'config' => ['title' => 'Call lead — no reply', 'due_in_days' => 1, 'assign_to_field' => 'assigned_user_id']],
    ];
    foreach ($steps as $i => $s) {
        $wf->steps()->create([...$s, 'order' => $i]);
    }

    return $wf;
}

it('runs the new-lead → wait → email → branch → task workflow end to end', function () {
    [$tenant, $owner] = autoWorkspace();
    $wf = followUpWorkflow();

    // QUEUE_CONNECTION=sync in tests, so the queued chain (incl. the delayed
    // wait step) executes inline when the lead is created.
    $lead = Lead::factory()->create(['status' => 'new', 'email' => 'dana@x.test', 'assigned_user_id' => $owner->id]);

    $run = WorkflowRun::where('workflow_id', $wf->id)->where('subject_id', $lead->id)->first();
    expect($run)->not->toBeNull()
        ->and($run->status)->toBe('completed');

    // Every step recorded as executed (idempotent log).
    expect($run->runSteps()->where('status', 'done')->count())->toBe(4);

    // The branch passed (lead still "new") so a follow-up task was created.
    $task = Task::where('subject_type', Lead::class)->where('subject_id', $lead->id)->first();
    expect($task)->not->toBeNull()
        ->and($task->assigned_user_id)->toBe($owner->id);
});

it('stops the workflow at the branch when the condition fails', function () {
    [$tenant, $owner] = autoWorkspace();
    followUpWorkflow();

    // Lead is already "qualified" → the "status == new" branch fails → no task.
    $lead = Lead::factory()->create(['status' => 'qualified', 'email' => 'q@x.test', 'assigned_user_id' => $owner->id]);

    $run = WorkflowRun::where('subject_id', $lead->id)->first();
    expect($run->status)->toBe('stopped');
    expect(Task::where('subject_id', $lead->id)->where('subject_type', Lead::class)->exists())->toBeFalse();
});

it('does not trigger workflows from other tenants', function () {
    [$acme, $acmeOwner] = autoWorkspace();
    followUpWorkflow();
    tenancy()->end();

    // A different tenant with no workflows.
    $globex = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Globex', 'owner_name' => 'Greg', 'email' => 'o@globex.test', 'password' => 'password',
    ]);
    tenancy()->initialize($globex);
    $lead = Lead::factory()->create(['status' => 'new', 'assigned_user_id' => $globex->getRelation('owner')->id]);

    // No workflow in Globex → no run created for its lead.
    expect(WorkflowRun::where('subject_id', $lead->id)->exists())->toBeFalse();
});
