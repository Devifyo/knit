<?php

declare(strict_types=1);

namespace App\Modules\Automation\Jobs;

use App\Models\Tag;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkflowRun;
use App\Models\WorkflowStep;
use App\Modules\Automation\Services\ConditionEvaluator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/**
 * Executes a WorkflowRun step-by-step. Idempotent (each step is recorded once in
 * workflow_run_steps and skipped on retry) and resumable (a `wait` step parks the
 * run and re-dispatches a delayed continuation). Runs are tenant-aware: the job
 * initializes the run's tenant before touching any scoped data.
 */
class RunWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $tenantId, public int $runId) {}

    public function handle(ConditionEvaluator $conditions): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (! $tenant) {
            return;
        }
        tenancy()->initialize($tenant);

        try {
            $run = WorkflowRun::with('workflow.steps', 'subject')->find($this->runId);
            if (! $run || in_array($run->status, ['completed', 'failed', 'stopped'], true)) {
                return;
            }

            /** @var Collection<int, WorkflowStep> $steps */
            $steps = $run->workflow->steps->values();
            $subject = $run->subject;

            for ($i = $run->current_step; $i < $steps->count(); $i++) {
                $step = $steps[$i];

                // Idempotency: skip steps already executed (retry-safe).
                if ($run->runSteps()->where('workflow_step_id', $step->id)->exists()) {
                    continue;
                }

                $config = $step->config ?? [];

                if ($step->type === 'wait') {
                    $this->record($run, $step, 'done', ['waited' => $config]);
                    $run->update(['current_step' => $i + 1, 'status' => 'waiting']);
                    static::dispatch($this->tenantId, $this->runId)->delay(now()->addSeconds($this->waitSeconds($config)));

                    return;
                }

                if ($step->type === 'condition') {
                    $passed = $conditions->evaluate($config['condition'] ?? null, $subject);
                    $this->record($run, $step, $passed ? 'done' : 'skipped', ['passed' => $passed]);
                    if (! $passed) {
                        $run->update(['status' => 'stopped', 'current_step' => $i + 1, 'finished_at' => now()]);

                        return;
                    }
                    $run->update(['current_step' => $i + 1, 'status' => 'running']);

                    continue;
                }

                $output = $this->runAction($step->type, $config, $subject, $run);
                $this->record($run, $step, 'done', $output);
                $run->update(['current_step' => $i + 1, 'status' => 'running']);
            }

            $run->update(['status' => 'completed', 'finished_at' => now()]);
        } finally {
            tenancy()->end();
        }
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function runAction(string $type, array $config, mixed $subject, WorkflowRun $run): array
    {
        return match ($type) {
            'send_email' => $this->sendEmail($config, $subject),
            'create_task' => $this->createTask($config, $subject),
            'update_field' => $this->updateField($config, $subject),
            'add_tag' => $this->addTag($config, $subject),
            'assign_owner' => $this->assignOwner($config, $subject),
            'webhook' => $this->callWebhook($config, $subject),
            default => ['noop' => $type],
        };
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function sendEmail(array $config, mixed $subject): array
    {
        // recipient: 'owner' emails the record's owner/assignee; otherwise the
        // configured field on the subject (default 'email').
        if (($config['recipient'] ?? null) === 'owner') {
            $ownerId = data_get($subject, 'owner_id') ?? data_get($subject, 'assigned_user_id');
            $to = $ownerId ? User::find($ownerId)?->email : null;
        } else {
            $to = data_get($subject, $config['to_field'] ?? 'email');
        }

        if (! $to) {
            return ['skipped' => 'no recipient'];
        }
        $body = (string) ($config['body'] ?? '');
        $subjectLine = (string) ($config['subject'] ?? 'A message from your CRM');
        Mail::raw($body, fn ($m) => $m->to($to)->subject($subjectLine));

        return ['email_to' => $to];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function createTask(array $config, mixed $subject): array
    {
        $task = Task::create([
            'title' => (string) ($config['title'] ?? 'Follow up'),
            'description' => $config['description'] ?? null,
            'due_at' => now()->addDays((int) ($config['due_in_days'] ?? 1)),
            'assigned_user_id' => data_get($subject, $config['assign_to_field'] ?? 'assigned_user_id'),
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
        ]);

        return ['task_id' => $task->id];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function updateField(array $config, mixed $subject): array
    {
        if (isset($config['field'])) {
            $subject->forceFill([$config['field'] => $config['value'] ?? null])->save();
        }

        return ['updated' => $config['field'] ?? null];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function addTag(array $config, mixed $subject): array
    {
        if (! empty($config['tag']) && method_exists($subject, 'tags')) {
            $tag = Tag::firstOrCreate(['name' => $config['tag']], ['color' => '#71717a']);
            $subject->tags()->syncWithoutDetaching([$tag->id]);

            return ['tag' => $config['tag']];
        }

        return ['skipped' => 'not taggable'];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function assignOwner(array $config, mixed $subject): array
    {
        $userId = $config['user_id'] ?? data_get($subject, $config['field'] ?? '');
        if ($userId && $subject->isFillable('owner_id')) {
            $subject->forceFill(['owner_id' => $userId])->save();
        }

        return ['owner_id' => $userId];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function callWebhook(array $config, mixed $subject): array
    {
        if (empty($config['url'])) {
            return ['skipped' => 'no url'];
        }
        Http::timeout(10)->post($config['url'], $config['payload'] ?? ['subject_id' => $subject->getKey()]);

        return ['posted' => $config['url']];
    }

    /** @param array<string, mixed> $config */
    protected function waitSeconds(array $config): int
    {
        return (int) ($config['seconds'] ?? 0)
            + (int) ($config['minutes'] ?? 0) * 60
            + (int) ($config['hours'] ?? 0) * 3600
            + (int) ($config['days'] ?? 0) * 86400;
    }

    /** @param array<string, mixed> $output */
    protected function record(WorkflowRun $run, WorkflowStep $step, string $status, array $output): void
    {
        $run->runSteps()->create([
            'workflow_step_id' => $step->id,
            'status' => $status,
            'output' => $output,
            'ran_at' => now(),
        ]);
    }
}
