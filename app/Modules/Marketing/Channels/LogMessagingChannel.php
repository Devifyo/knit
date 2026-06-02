<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Channels;

use Illuminate\Support\Facades\Log;

/**
 * Stub channel used until a real SMS/WhatsApp provider is wired (Phase 10).
 * Records the would-be send so flows are testable end-to-end without a provider.
 */
class LogMessagingChannel implements MessagingChannel
{
    public function channel(): string
    {
        return 'log';
    }

    public function send(string $to, string $message): bool
    {
        Log::info('[messaging stub] would send', ['to' => $to, 'message' => $message]);

        return true;
    }
}
