<?php

declare(strict_types=1);

namespace App\Modules\Leads\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Modules\Leads\Jobs\ScoreLeadJob;
use App\Modules\Leads\Services\LeadConversionService;
use App\Services\AI\GeminiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    /** AI lead scoring — updates the lead's score + reasons from real lead data. */
    public function score(Lead $lead, GeminiService $ai): RedirectResponse
    {
        $result = $ai->scoreLead($lead);

        $lead->forceFill([
            'score' => $result['score'],
            'custom_fields' => [...($lead->custom_fields ?? []), 'ai_reasons' => $result['reasons']],
        ])->save();

        return back()->with('success', "AI scored this lead {$result['score']}/100.");
    }

    public function index(Request $request): Response
    {
        $leads = Lead::with('assignee:id,name')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (Lead $l) => [
                'id' => $l->id,
                'name' => $l->name,
                'email' => $l->email,
                'source' => $l->source,
                'source_url' => $l->source_url,
                'status' => $l->status,
                'score' => $l->score,
                'assignee' => $l->assignee?->name,
                'converted' => $l->isConverted(),
            ])->all();

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'captureUrl' => url('/f/'.tenant('slug')),
        ]);
    }

    public function show(Lead $lead): Response
    {
        $lead->load('assignee:id,name', 'activities.user:id,name', 'contact:id,first_name,last_name');

        $fields = $lead->custom_fields ?? [];

        return Inertia::render('Leads/Show', [
            'lead' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'source' => $lead->source,
                'source_url' => $lead->source_url,
                'status' => $lead->status,
                'score' => $lead->score,
                'reasons' => $fields['ai_reasons'] ?? [],
                'message' => $fields['message'] ?? null,
                'assignee' => $lead->assignee?->name,
                'created_at' => $lead->created_at?->toFormattedDateString(),
                'converted' => $lead->isConverted(),
                'contact_id' => $lead->converted_to_contact_id,
                'contact_name' => $lead->contact?->name,
            ],
            'activities' => $lead->activities->map(fn ($a) => [
                'id' => $a->id,
                'type' => $a->type,
                'body' => $a->body,
                'author' => optional($a->user)->name ?? 'System',
                'at' => $a->created_at?->diffForHumans(),
            ]),
        ]);
    }

    public function store(Request $request, GeminiService $ai): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($data['email']) && Lead::where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => 'A lead with this email already exists.']);
        }

        $lead = Lead::create([
            ...$data,
            'source' => ($data['source'] ?? null) ?: 'Manual entry',
            'status' => 'new',
            'assigned_user_id' => $request->user()->id,
            // Immediate signal-based score; the queued job refines it with AI.
            'score' => $ai->heuristicLeadScore($data),
        ]);

        ScoreLeadJob::dispatch((string) tenant('id'), $lead->id);

        return back()->with('success', 'Lead captured & scored.');
    }

    public function convert(Request $request, Lead $lead, LeadConversionService $service): RedirectResponse
    {
        $result = $service->convert($lead);

        return redirect("/contacts/{$result['contact']->id}")
            ->with('success', 'Lead converted to contact'.($result['deal'] ? ' + deal.' : '.'));
    }
}
