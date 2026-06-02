<?php

declare(strict_types=1);

namespace App\Modules\Support\Channels;

use App\Modules\Support\Contracts\ChannelAdapter;
use Illuminate\Support\Str;

class EmailChannelAdapter implements ChannelAdapter
{
    public function channel(): string
    {
        return 'email';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{from_email:?string, from_name:?string, subject:string, body:string}
     */
    public function normalize(array $payload): array
    {
        return [
            'from_email' => $payload['from_email'] ?? null,
            'from_name' => $payload['from_name'] ?? (isset($payload['from_email']) ? Str::before((string) $payload['from_email'], '@') : null),
            'subject' => (string) ($payload['subject'] ?? '(no subject)'),
            'body' => (string) ($payload['body'] ?? ''),
        ];
    }
}
