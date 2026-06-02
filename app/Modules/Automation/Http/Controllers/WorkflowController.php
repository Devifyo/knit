<?php

declare(strict_types=1);

namespace App\Modules\Automation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkflowController extends Controller
{
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'template' => ['nullable', 'string'],
        ]);

        $workflow = Workflow::create([
            'name' => $data['name'],
            'trigger_event' => 'lead.created',
            'enabled' => true,
        ]);

        // Ship the canonical follow-up sequence:
        // new lead -> wait 1 day -> send email -> if still "new", create a task.
        $steps = [
            ['type' => 'wait', 'config' => ['days' => 1]],
            ['type' => 'send_email', 'config' => ['to_field' => 'email', 'subject' => 'Thanks for your interest', 'body' => 'Hi — following up on your enquiry. How can we help?']],
            ['type' => 'condition', 'config' => ['condition' => ['operator' => 'and', 'rules' => [['field' => 'status', 'op' => 'equals', 'value' => 'new']]]]],
            ['type' => 'create_task', 'config' => ['title' => 'Call lead — no reply to follow-up', 'due_in_days' => 1, 'assign_to_field' => 'assigned_user_id']],
        ];
        foreach ($steps as $i => $step) {
            $workflow->steps()->create([...$step, 'order' => $i]);
        }

        return back()->with('success', 'Workflow created.');
    }

    public function toggle(Workflow $workflow): RedirectResponse
    {
        $workflow->update(['enabled' => ! $workflow->enabled]);

        return back()->with('success', $workflow->enabled ? 'Workflow enabled.' : 'Workflow disabled.');
    }
}
