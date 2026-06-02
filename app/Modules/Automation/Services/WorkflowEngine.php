<?php

declare(strict_types=1);

namespace App\Modules\Automation\Services;

use App\Models\Workflow;
use App\Models\WorkflowRun;
use App\Modules\Automation\Jobs\RunWorkflowJob;
use Illuminate\Database\Eloquent\Model;

/**
 * Entry point for the automation engine. When a domain event fires, this starts
 * a WorkflowRun for every enabled workflow listening on that event and queues
 * the first step. Step execution itself lives in {@see RunWorkflowJob}.
 */
class WorkflowEngine
{
    public function trigger(string $event, Model $subject): void
    {
        // Hard guard: never run unscoped (which would match workflows across all
        // tenants). Triggers always fire inside an active tenant context.
        if (! function_exists('tenant') || ! tenant()) {
            return;
        }

        $workflows = Workflow::where('trigger_event', $event)->where('enabled', true)->with('steps')->get();

        foreach ($workflows as $workflow) {
            if ($workflow->steps->isEmpty()) {
                continue;
            }

            $run = WorkflowRun::create([
                'workflow_id' => $workflow->id,
                'subject_type' => $subject::class,
                'subject_id' => $subject->getKey(),
                'status' => 'running',
                'current_step' => 0,
            ]);

            RunWorkflowJob::dispatch($run->tenant_id, $run->id);
        }
    }
}
