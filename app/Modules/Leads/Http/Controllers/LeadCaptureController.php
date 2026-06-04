<?php

declare(strict_types=1);

namespace App\Modules\Leads\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Tenant;
use App\Modules\Leads\Jobs\ScoreLeadJob;
use App\Services\AI\GeminiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public, unauthenticated lead-capture form for a workspace (resolved by slug).
 * Submissions create a Lead inside that workspace, which fires the `lead.created`
 * trigger — so a public form feeds straight into the automation engine.
 */
class LeadCaptureController extends Controller
{
    public function show(string $slug): Response
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        return Inertia::render('Leads/Capture', [
            'workspace' => ['name' => $tenant->name, 'slug' => $tenant->slug, 'brand_color' => $tenant->brand_color],
            'submitted' => session('submitted', false),
        ]);
    }

    public function submit(Request $request, string $slug): RedirectResponse
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        tenancy()->initialize($tenant);
        try {
            // Dedupe on email within the workspace.
            if (! Lead::where('email', $data['email'])->exists()) {
                $payload = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'source' => 'Capture form',
                    'source_url' => url('/f/'.$tenant->slug),
                ];
                $lead = Lead::create([
                    ...$payload,
                    'status' => 'new',
                    'score' => app(GeminiService::class)->heuristicLeadScore($payload),
                    'custom_fields' => ! empty($data['message']) ? ['message' => $data['message']] : null,
                ]);
                ScoreLeadJob::dispatch((string) $tenant->getTenantKey(), $lead->id);
            }
        } finally {
            tenancy()->end();
        }

        return back()->with('submitted', true);
    }
}
