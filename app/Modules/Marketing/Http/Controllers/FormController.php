<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Workflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FormController extends Controller
{
    public function index(): Response
    {
        $forms = Form::with('nurtureWorkflow:id,name')->latest()->get()
            ->map(fn (Form $f): array => [
                'id' => $f->id,
                'name' => $f->name,
                'url' => url('/forms/'.$f->slug),
                'fields' => count($f->fields ?? []),
                'submissions' => $f->submissions_count,
                'nurture' => $f->nurtureWorkflow?->name,
            ])->all();

        return Inertia::render('Forms/Index', [
            'forms' => $forms,
            'workflows' => Workflow::get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nurture_workflow_id' => ['nullable', 'exists:workflows,id'],
            'fields' => ['array'],
        ]);

        Form::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'fields' => $data['fields'] ?? [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => false],
            ],
            'nurture_workflow_id' => $data['nurture_workflow_id'] ?? null,
        ]);

        return back()->with('success', 'Form created — share its public URL.');
    }
}
