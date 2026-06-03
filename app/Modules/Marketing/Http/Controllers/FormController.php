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
        $data = $request->validate($this->rules());

        Form::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'fields' => $this->buildFields($data['fields'] ?? []),
            'nurture_workflow_id' => $data['nurture_workflow_id'] ?? null,
        ]);

        return back()->with('success', 'Form created — share its public URL.');
    }

    public function update(Request $request, Form $form): RedirectResponse
    {
        $data = $request->validate($this->rules());

        // Slug (public URL) stays stable across edits so shared links keep working.
        $form->update([
            'name' => $data['name'],
            'fields' => $this->buildFields($data['fields'] ?? []),
            'nurture_workflow_id' => $data['nurture_workflow_id'] ?? null,
        ]);

        return back()->with('success', 'Form updated.');
    }

    /**
     * Validation rules shared by store + update. The caller sends only the *extra*
     * fields (in display order); Name + Email are always added by buildFields().
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nurture_workflow_id' => ['nullable', 'exists:workflows,id'],
            'fields' => ['array'],
            'fields.*.label' => ['required', 'string', 'max:80'],
            'fields.*.type' => ['required', Rule::in(['text', 'email', 'number', 'tel', 'date', 'datetime'])],
            'fields.*.required' => ['boolean'],
        ];
    }

    /**
     * Build the stored field schema: Name + Email are always present (the lead
     * intake depends on them), followed by the workspace's custom fields. Keys are
     * derived from labels server-side and de-duplicated.
     *
     * @param  array<int, array{label: string, type: string, required?: bool}>  $custom
     * @return array<int, array{key: string, label: string, type: string, required: bool}>
     */
    protected function buildFields(array $custom): array
    {
        $fields = [
            ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
        ];
        $seen = ['name' => true, 'email' => true];

        foreach ($custom as $field) {
            $key = Str::slug($field['label'], '_');
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $fields[] = [
                'key' => $key,
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => (bool) ($field['required'] ?? false),
            ];
        }

        return $fields;
    }
}
