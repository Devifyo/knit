<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Services;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Lead;
use App\Modules\Automation\Services\WorkflowEngine;
use App\Modules\Leads\Jobs\ScoreLeadJob;
use App\Services\AI\GeminiService;

/**
 * Handles a public marketing-form submission: creates a linked Lead (which fires
 * lead.created, so any matching workflow runs), records the submission, and — if
 * the form is wired to a nurture sequence — explicitly enrols the lead into it.
 *
 * Requires an active tenant context.
 */
class FormIntakeService
{
    public function __construct(protected WorkflowEngine $engine) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(Form $form, array $payload): Lead
    {
        $name = (string) ($payload['name'] ?? trim(($payload['first_name'] ?? '').' '.($payload['last_name'] ?? '')) ?: 'Website lead');
        $email = $payload['email'] ?? null;

        // Dedupe on email within the workspace.
        $lead = $email ? Lead::where('email', $email)->first() : null;
        if (! $lead) {
            $attrs = [
                'name' => $name,
                'email' => $email,
                'phone' => $payload['phone'] ?? null,
                'source' => 'Form: '.$form->name,
                'source_url' => url('/forms/'.$form->slug),
            ];
            $lead = Lead::create([
                ...$attrs,
                'status' => 'new',
                'score' => app(GeminiService::class)->heuristicLeadScore($attrs),
                'custom_fields' => $payload,
            ]);
            ScoreLeadJob::dispatch((string) tenant('id'), $lead->id);
        }

        FormSubmission::create(['form_id' => $form->id, 'lead_id' => $lead->id, 'payload' => $payload]);
        $form->increment('submissions_count');

        // Enrol into the form's nurture sequence (in addition to any lead.created workflows).
        if ($form->nurture_workflow_id && $form->nurtureWorkflow) {
            $this->engine->startWorkflow($form->nurtureWorkflow, $lead);
        }

        return $lead;
    }
}
