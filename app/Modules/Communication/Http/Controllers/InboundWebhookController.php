<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Communication\Services\InboundEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public inbound-email webhook (e.g. Mailgun/Postmark route). Resolves the
 * workspace by slug, then threads the email into its shared inbox.
 */
class InboundWebhookController extends Controller
{
    public function __invoke(Request $request, string $slug, InboundEmailService $service): JsonResponse
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'from_email' => ['required', 'email'],
            'from_name' => ['nullable', 'string'],
            'subject' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'message_id' => ['nullable', 'string'],
            'in_reply_to' => ['nullable', 'string'],
        ]);

        tenancy()->initialize($tenant);
        try {
            $message = $service->handle($data);
        } finally {
            tenancy()->end();
        }

        return response()->json(['ok' => true, 'conversation_id' => $message->conversation_id], 201);
    }
}
