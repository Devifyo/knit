<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Modules\Marketing\Jobs\SendCampaignJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function index(): Response
    {
        $campaigns = Campaign::withCount([
            'recipients',
            'recipients as opens' => fn ($q) => $q->whereNotNull('opened_at'),
            'recipients as clicks' => fn ($q) => $q->whereNotNull('clicked_at'),
        ])->latest()->get()->map(fn (Campaign $c): array => [
            'id' => $c->id,
            'name' => $c->name,
            'status' => $c->status,
            'sent' => $c->recipients_count,
            'opens' => (int) $c->getAttribute('opens'),
            'clicks' => (int) $c->getAttribute('clicks'),
            'sent_at' => $c->sent_at?->toFormattedDateString(),
        ])->all();

        return Inertia::render('Campaigns/Index', ['campaigns' => $campaigns]);
    }

    public function show(Campaign $campaign): Response
    {
        $sent = $campaign->recipients()->count();
        $opens = $campaign->recipients()->whereNotNull('opened_at')->count();
        $clicks = $campaign->recipients()->whereNotNull('clicked_at')->count();
        $rate = fn (int $n) => $sent > 0 ? round($n / $sent * 100, 1) : 0.0;

        $variants = $campaign->isAbTest()
            ? collect(['A', 'B'])->map(fn ($v) => [
                'variant' => $v,
                'subject' => $v === 'A' ? $campaign->subject : $campaign->subject_b,
                'sent' => $campaign->recipients()->where('variant', $v)->count(),
                'opens' => $campaign->recipients()->where('variant', $v)->whereNotNull('opened_at')->count(),
            ])->all()
            : [];

        return Inertia::render('Campaigns/Show', [
            'campaign' => [
                'id' => $campaign->id, 'name' => $campaign->name, 'subject' => $campaign->subject,
                'status' => $campaign->status, 'audience' => $campaign->audience, 'ab' => $campaign->isAbTest(),
            ],
            'stats' => [
                'sent' => $sent, 'opens' => $opens, 'clicks' => $clicks,
                'open_rate' => $rate($opens), 'click_rate' => $rate($clicks),
            ],
            'variants' => $variants,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'subject_b' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_url' => ['nullable', 'url'],
            'audience' => ['required', 'in:contacts,leads'],
        ]);

        $campaign = Campaign::create([...$data, 'status' => 'draft']);

        return redirect("/campaigns/{$campaign->id}")->with('success', 'Campaign created.');
    }

    public function send(Campaign $campaign): RedirectResponse
    {
        if ($campaign->status === 'sent') {
            return back()->with('error', 'Campaign already sent.');
        }

        SendCampaignJob::dispatch($campaign->tenant_id, $campaign->id);

        return back()->with('success', 'Campaign is sending — analytics will populate as it goes.');
    }
}
