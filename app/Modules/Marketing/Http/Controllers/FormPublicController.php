<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Tenant;
use App\Modules\Marketing\Services\FormIntakeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public landing-page form. A submission creates a linked Lead and enrols it into
 * the form's nurture sequence (Phase 3 engine).
 */
class FormPublicController extends Controller
{
    public function show(string $slug): Response
    {
        [$tenant, $form] = $this->resolve($slug);

        return Inertia::render('Forms/Public', [
            'workspace' => ['name' => $tenant->name, 'brand_color' => $tenant->brand_color],
            'form' => ['slug' => $form->slug, 'name' => $form->name, 'fields' => $form->fields, 'success' => $form->success_message],
            'submitted' => session('submitted', false),
        ]);
    }

    public function submit(Request $request, string $slug, FormIntakeService $intake): RedirectResponse
    {
        [$tenant, $form] = $this->resolve($slug);

        $rules = [];
        foreach ($form->fields ?? [] as $field) {
            $rules[$field['key']] = ($field['required'] ?? false ? 'required|' : 'nullable|')
                .($field['type'] === 'email' ? 'email' : 'string').'|max:500';
        }
        $payload = $request->validate($rules);

        tenancy()->initialize($tenant);
        try {
            $intake->handle($form, $payload);
        } finally {
            tenancy()->end();
        }

        return back()->with('submitted', true);
    }

    /**
     * @return array{0: Tenant, 1: Form}
     */
    protected function resolve(string $slug): array
    {
        // Public route (no tenant context): find the form by slug across tenants.
        $form = Form::withoutGlobalScopes()->where('slug', $slug)->firstOrFail();
        $tenant = Tenant::findOrFail($form->tenant_id);

        return [$tenant, $form];
    }
}
