<?php

declare(strict_types=1);

namespace App\Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Support\Channels\EmailChannelAdapter;
use App\Modules\Support\Services\TicketIntakeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public support intake webhook (e.g. support@ mailbox routing). Creates a
 * routed, SLA-timed ticket in the workspace resolved by slug.
 */
class SupportWebhookController extends Controller
{
    public function __invoke(Request $request, string $slug, TicketIntakeService $intake): JsonResponse
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'from_email' => ['required', 'email'],
            'from_name' => ['nullable', 'string'],
            'subject' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
        ]);

        tenancy()->initialize($tenant);
        try {
            $ticket = $intake->fromChannel(new EmailChannelAdapter, $data, $data['priority'] ?? 'normal');
        } finally {
            tenancy()->end();
        }

        return response()->json(['ok' => true, 'ticket' => $ticket->number], 201);
    }
}
