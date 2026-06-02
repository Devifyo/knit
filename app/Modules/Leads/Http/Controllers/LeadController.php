<?php

declare(strict_types=1);

namespace App\Modules\Leads\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Modules\Leads\Services\LeadConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:255'],
        ]);

        // Duplicate detection on email/phone.
        if (! empty($data['email']) && Lead::where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => 'A lead with this email already exists.']);
        }

        Lead::create([
            ...$data,
            'status' => 'new',
            'assigned_user_id' => $request->user()->id,
            // Lightweight scoring stub until GeminiService::scoreLead lands (Phase 7).
            'score' => ! empty($data['email']) ? 40 : 10,
        ]);

        return back()->with('success', 'Lead captured.');
    }

    public function convert(Request $request, Lead $lead, LeadConversionService $service): RedirectResponse
    {
        $result = $service->convert($lead);

        return redirect("/contacts/{$result['contact']->id}")
            ->with('success', 'Lead converted to contact'.($result['deal'] ? ' + deal.' : '.'));
    }
}
