<?php

declare(strict_types=1);

namespace App\Modules\Industry\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ModuleRecord;
use App\Modules\Deals\Services\PricingService;
use App\Modules\Industry\Services\ModuleRegistry;
use App\Modules\Industry\Services\Modules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Generic CRUD for any enabled module entity. Validation, columns and the create
 * form are all derived from the ModuleRegistry manifest, so every industry
 * module is fully functional through this one controller.
 */
class ModuleRecordController extends Controller
{
    public function __construct(
        private readonly Modules $modules,
        private readonly PricingService $money,
    ) {}

    public function index(string $module, string $entity): Response
    {
        [$mod, $ent] = $this->resolve($module, $entity);

        $records = ModuleRecord::with('contact:id,first_name,last_name', 'owner:id,name')
            ->where('module_key', $module)->where('entity_key', $entity)
            ->latest()->get()
            ->map(fn (ModuleRecord $r): array => [
                'id' => $r->id,
                'title' => $r->title,
                'status' => $r->status,
                'contact' => $r->contact?->name,
                'contact_id' => $r->contact_id,
                'owner' => $r->owner?->name,
                'values' => $this->displayValues($ent, $r),
            ])->all();

        return Inertia::render('Modules/Index', [
            'module' => ['key' => $mod['key'], 'name' => $mod['name'], 'icon' => $mod['icon'], 'hipaa' => (bool) ($mod['hipaa'] ?? false)],
            'entity' => $ent,
            'records' => $records,
            'contacts' => ($ent['links_contact'] ?? false)
                ? Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name'])
                    ->map(fn (Contact $c) => ['id' => $c->id, 'name' => $c->name])
                : [],
            'can_manage' => $this->userCan('modules.use'),
        ]);
    }

    public function store(Request $request, string $module, string $entity): RedirectResponse
    {
        [, $ent] = $this->resolve($module, $entity);

        $rules = ['contact_id' => ['nullable', 'exists:contacts,id']];
        foreach ($ent['fields'] as $field) {
            $rules[$field['key']] = $this->rulesFor($field);
        }
        $validated = $request->validate($rules);

        $data = [];
        foreach ($ent['fields'] as $field) {
            $value = $validated[$field['key']] ?? null;
            $data[$field['key']] = $field['type'] === 'money' && $value !== null
                ? (int) round((float) $value * 100) // store money as integer minor units
                : $value;
        }

        ModuleRecord::create([
            'module_key' => $module,
            'entity_key' => $entity,
            'title' => (string) ($data[$ent['title_field']] ?? $ent['singular']),
            'status' => isset($ent['status_field']) ? ($data[$ent['status_field']] ?? null) : null,
            'owner_id' => $request->user()->id,
            'contact_id' => $validated['contact_id'] ?? null,
            'data' => $data,
        ]);

        return back()->with('success', $ent['singular'].' added.');
    }

    public function destroy(string $module, string $entity, ModuleRecord $record): RedirectResponse
    {
        abort_unless($record->module_key === $module && $record->entity_key === $entity, 404);
        $record->delete();

        return back()->with('success', 'Record removed.');
    }

    /**
     * Resolve + guard: the entity must exist and its module must be enabled for
     * this tenant, otherwise 404 (a disabled module is invisible).
     *
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function resolve(string $module, string $entity): array
    {
        $ent = ModuleRegistry::entity($module, $entity);
        abort_if($ent === null || ! $this->modules->isEnabled($module), 404);

        /** @var array<string, mixed> $mod */
        $mod = ModuleRegistry::find($module);

        return [$mod, $ent];
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<int, mixed>
     */
    private function rulesFor(array $field): array
    {
        $rules = [($field['required'] ?? false) ? 'required' : 'nullable'];

        $rules[] = match ($field['type']) {
            'money', 'number' => 'numeric',
            'date' => 'date',
            'select' => Rule::in($field['options'] ?? []),
            default => 'string',
        };
        if (in_array($field['type'], ['text', 'textarea'], true)) {
            $rules[] = 'max:2000';
        }

        return $rules;
    }

    /**
     * Human-readable values for the index table (money formatted, etc.).
     *
     * @param  array<string, mixed>  $entity
     * @return array<string, string>
     */
    private function displayValues(array $entity, ModuleRecord $record): array
    {
        $out = [];
        foreach ($entity['fields'] as $field) {
            $value = $record->data[$field['key']] ?? null;
            $out[$field['key']] = $field['type'] === 'money' && $value !== null
                ? $this->money->format((int) $value, 'USD')
                : (string) ($value ?? '—');
        }

        return $out;
    }

    private function userCan(string $permission): bool
    {
        $user = request()->user();

        return $user !== null && $user->can($permission);
    }
}
