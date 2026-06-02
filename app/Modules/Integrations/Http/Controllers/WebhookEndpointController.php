<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Modules\Integrations\Jobs\DeliverWebhookJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WebhookEndpointController extends Controller
{
    /** Events a tenant can subscribe an endpoint to, grouped for the UI. */
    public const EVENT_GROUPS = [
        'Contacts' => ['contact.created', 'contact.updated', 'contact.deleted'],
        'Companies' => ['company.created', 'company.updated'],
        'Leads' => ['lead.created', 'lead.updated', 'lead.converted'],
        'Deals' => ['deal.created', 'deal.updated', 'deal.won', 'deal.lost'],
        'Sales' => ['quote.accepted', 'invoice.paid'],
        'Support' => ['ticket.created'],
        'Productivity' => ['task.completed', 'project.created'],
    ];

    /** @return array<int, string> */
    public static function events(): array
    {
        return array_merge(...array_values(self::EVENT_GROUPS));
    }

    public function index(): Response
    {
        return Inertia::render('Settings/Webhooks', [
            'eventGroups' => collect(self::EVENT_GROUPS)->map(fn (array $events, string $group) => [
                'group' => $group,
                'events' => $events,
            ])->values(),
            'endpoints' => WebhookEndpoint::withCount('deliveries')->latest()->get()->map(fn (WebhookEndpoint $e): array => [
                'id' => $e->id,
                'url' => $e->url,
                'events' => $e->events,
                'active' => $e->active,
                'deliveries' => $e->getAttribute('deliveries_count'),
            ]),
            'deliveries' => WebhookDelivery::with('endpoint:id,url')->latest()->limit(20)->get()->map(fn (WebhookDelivery $d): array => [
                'id' => $d->id,
                'event' => $d->event,
                'url' => $d->endpoint?->url,
                'success' => $d->success,
                'status_code' => $d->status_code,
                'attempts' => $d->attempts,
                'at' => $d->created_at?->diffForHumans(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'in:'.implode(',', self::events())],
        ]);

        $secret = 'whsec_'.Str::random(40);
        WebhookEndpoint::create([
            'url' => $data['url'],
            'events' => $data['events'],
            'secret' => $secret,
            'created_by' => $request->user()->id,
        ]);

        // The signing secret is shown once so the receiver can verify signatures.
        return back()->with('success', "Endpoint added. Signing secret (shown once): {$secret}");
    }

    public function destroy(WebhookEndpoint $endpoint): RedirectResponse
    {
        $endpoint->delete();

        return back()->with('success', 'Endpoint removed.');
    }

    /** Fire a sample event at the endpoint so the receiver can confirm wiring. */
    public function ping(WebhookEndpoint $endpoint): RedirectResponse
    {
        $delivery = $endpoint->deliveries()->create([
            'event' => 'ping',
            'payload' => json_encode(['event' => 'ping', 'data' => ['message' => 'Hello from Knit']], JSON_THROW_ON_ERROR),
        ]);
        DeliverWebhookJob::dispatch(tenant('id'), $delivery->id);

        return back()->with('success', 'Test event queued.');
    }
}
