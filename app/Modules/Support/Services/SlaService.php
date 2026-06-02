<?php

declare(strict_types=1);

namespace App\Modules\Support\Services;

use Carbon\CarbonInterface;

/**
 * First-response SLA policy by priority (minutes). Drives the SLA countdown and
 * the breach check that triggers escalation.
 */
class SlaService
{
    /** @var array<string, int> */
    public const RESPONSE_MINUTES = [
        'urgent' => 30,
        'high' => 120,
        'normal' => 480,
        'low' => 1440,
    ];

    public function minutesFor(string $priority): int
    {
        return self::RESPONSE_MINUTES[$priority] ?? self::RESPONSE_MINUTES['normal'];
    }

    public function dueAt(string $priority): CarbonInterface
    {
        return now()->addMinutes($this->minutesFor($priority));
    }
}
