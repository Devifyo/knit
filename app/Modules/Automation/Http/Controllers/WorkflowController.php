<?php

declare(strict_types=1);

namespace App\Modules\Automation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class WorkflowController extends Controller
{
    private const TRIGGERS = [
        ['value' => 'lead.created', 'label' => 'Lead created'],
        ['value' => 'contact.created', 'label' => 'Contact created'],
        ['value' => 'deal.created', 'label' => 'Deal created'],
    ];

    private const STEP_TYPES = [
        ['value' => 'wait', 'label' => 'Wait / delay'],
        ['value' => 'send_email', 'label' => 'Send email'],
        ['value' => 'create_task', 'label' => 'Create task'],
        ['value' => 'condition', 'label' => 'If / else (branch)'],
        ['value' => 'add_tag', 'label' => 'Add tag'],
        ['value' => 'update_field', 'label' => 'Update field'],
        ['value' => 'assign_owner', 'label' => 'Assign owner'],
        ['value' => 'webhook', 'label' => 'Call webhook'],
    ];

    public function index(): Response
    {
        $workflows = Workflow::withCount(['steps', 'runs'])->with('steps:id,workflow_id,type,order')->latest()->get()
            ->map(fn (Workflow $w) => [
                'id' => $w->id,
                'name' => $w->name,
                'trigger_event' => $w->trigger_event,
                'enabled' => $w->enabled,
                'steps' => $w->steps->map(fn ($s) => $s->type),
                'runs_count' => $w->runs_count,
            ])->all();

        return Inertia::render('Workflows/Index', ['workflows' => $workflows]);
    }

    public function create(): Response
    {
        return Inertia::render('Workflows/Builder', [
            'workflow' => null,
            'triggers' => self::TRIGGERS,
            'stepTypes' => self::STEP_TYPES,
        ]);
    }

    public function edit(Workflow $workflow): Response
    {
        $workflow->load('steps');

        return Inertia::render('Workflows/Builder', [
            'workflow' => [
                'id' => $workflow->id,
                'name' => $workflow->name,
                'trigger_event' => $workflow->trigger_event,
                'enabled' => $workflow->enabled,
                'steps' => $workflow->steps->map(fn ($s) => ['type' => $s->type, 'config' => $s->config ?? []]),
            ],
            'triggers' => self::TRIGGERS,
            'stepTypes' => self::STEP_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateWorkflow($request);

        $workflow = Workflow::create([
            'name' => $data['name'],
            'trigger_event' => $data['trigger_event'],
            'enabled' => $data['enabled'] ?? true,
        ]);
        $this->syncSteps($workflow, $data['steps'] ?? []);

        return redirect('/workflows')->with('success', 'Workflow created.');
    }

    public function update(Request $request, Workflow $workflow): RedirectResponse
    {
        $data = $this->validateWorkflow($request);

        $workflow->update([
            'name' => $data['name'],
            'trigger_event' => $data['trigger_event'],
            'enabled' => $data['enabled'] ?? $workflow->enabled,
        ]);
        $this->syncSteps($workflow, $data['steps'] ?? []);

        return redirect('/workflows')->with('success', 'Workflow updated.');
    }

    public function toggle(Workflow $workflow): RedirectResponse
    {
        $workflow->update(['enabled' => ! $workflow->enabled]);

        return back()->with('success', $workflow->enabled ? 'Workflow enabled.' : 'Workflow disabled.');
    }

    public function destroy(Workflow $workflow): RedirectResponse
    {
        $workflow->delete();

        return redirect('/workflows')->with('success', 'Workflow deleted.');
    }

    /** @return array<string, mixed> */
    protected function validateWorkflow(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trigger_event' => ['required', 'string', 'max:64'],
            'enabled' => ['boolean'],
            'steps' => ['array'],
            'steps.*.type' => ['required', 'string', 'max:32'],
            'steps.*.config' => ['nullable', 'array'],
        ]);
    }

    /** @param array<int, array<string, mixed>> $steps */
    protected function syncSteps(Workflow $workflow, array $steps): void
    {
        DB::transaction(function () use ($workflow, $steps): void {
            $workflow->steps()->delete();
            foreach (array_values($steps) as $i => $step) {
                $workflow->steps()->create([
                    'type' => $step['type'],
                    'config' => $step['config'] ?? [],
                    'order' => $i,
                ]);
            }
        });
    }
}
