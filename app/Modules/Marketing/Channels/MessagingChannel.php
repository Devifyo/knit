<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Channels;

/**
 * Outbound messaging channel (SMS / WhatsApp). Real providers (Twilio, Meta WA
 * Cloud API) implement this; the pipeline that calls send() never changes.
 */
interface MessagingChannel
{
    public function channel(): string;

    public function send(string $to, string $message): bool;
}
