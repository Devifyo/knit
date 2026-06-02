<?php

declare(strict_types=1);

namespace App\Modules\Support\Contracts;

/**
 * Normalizes an inbound message from any support channel (email, chat, WhatsApp,
 * Messenger, IG, voice) into a common shape the intake service understands.
 * Add a new adapter per channel; the intake pipeline stays unchanged.
 */
interface ChannelAdapter
{
    public function channel(): string;

    /**
     * @param  array<string, mixed>  $payload
     * @return array{from_email:?string, from_name:?string, subject:string, body:string}
     */
    public function normalize(array $payload): array;
}
