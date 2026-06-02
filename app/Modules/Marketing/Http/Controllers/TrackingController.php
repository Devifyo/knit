<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CampaignRecipient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Public email open/click tracking. Open = 1x1 pixel; click = redirect after
 * recording. Looked up across tenants by the unguessable per-recipient token.
 */
class TrackingController extends Controller
{
    public function open(string $token): Response
    {
        $recipient = CampaignRecipient::withoutGlobalScopes()->where('token', $token)->first();
        if ($recipient && $recipient->opened_at === null) {
            $recipient->forceFill(['opened_at' => now()])->save();
        }

        // 1x1 transparent GIF.
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, ['Content-Type' => 'image/gif', 'Cache-Control' => 'no-store']);
    }

    public function click(Request $request, string $token): RedirectResponse
    {
        $recipient = CampaignRecipient::withoutGlobalScopes()->where('token', $token)->first();
        if ($recipient) {
            if ($recipient->clicked_at === null) {
                $recipient->forceFill(['clicked_at' => now(), 'opened_at' => $recipient->opened_at ?? now()])->save();
            }
        }

        $url = $request->query('url');

        return redirect()->away(is_string($url) && $url !== '' ? $url : '/');
    }
}
