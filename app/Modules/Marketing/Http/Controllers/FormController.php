<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Workflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
                'schema' => $f->fields ?? [],
                'nurture_workflow_id' => $f->nurture_workflow_id,
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
        $data = $request->validate($this->rules(), $this->messages());

        Form::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'fields' => $this->normalizeFields($data['fields']),
            'nurture_workflow_id' => $data['nurture_workflow_id'] ?? null,
        ]);

        return back()->with('success', 'Form created — share its public URL.');
    }

    public function update(Request $request, Form $form): RedirectResponse
    {
        $data = $request->validate($this->rules(), $this->messages());

        // Slug (public URL) stays stable across edits so shared links keep working.
        $form->update([
            'name' => $data['name'],
            'fields' => $this->normalizeFields($data['fields']),
            'nurture_workflow_id' => $data['nurture_workflow_id'] ?? null,
        ]);

        return back()->with('success', 'Form updated.');
    }

    /**
     * Validation rules shared by store + update. Every field is fully
     * customizable; the form just needs at least one.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nurture_workflow_id' => ['nullable', 'exists:workflows,id'],
            'fields' => ['array', 'min:1'],
            'fields.*.label' => ['required', 'string', 'max:80'],
            'fields.*.type' => ['required', Rule::in(['text', 'email', 'number', 'tel', 'date', 'datetime'])],
            'fields.*.required' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return ['fields.min' => 'Add at least one field to your form.'];
    }

    /**
     * Normalize the submitted fields (preserving order): derive a stable key from
     * each label and drop duplicate keys. Nothing is forced — a workspace can
     * include, rename, reorder or omit any field (the lead intake reads the `name`
     * and `email` keys when present and falls back gracefully when they're not).
     *
     * @param  array<int, array{label: string, type: string, required?: bool}>  $fields
     * @return array<int, array{key: string, label: string, type: string, required: bool}>
     */
    protected function normalizeFields(array $fields): array
    {
        $out = [];
        $seen = [];

        foreach ($fields as $field) {
            $key = Str::slug($field['label'], '_');
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = [
                'key' => $key,
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => (bool) ($field['required'] ?? false),
            ];
        }

        return $out;
    }
}
