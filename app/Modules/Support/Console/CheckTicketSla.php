<?php

declare(strict_types=1);

namespace App\Modules\Support\Console;

use App\Models\Tenant;
use App\Modules\Support\Services\EscalationService;
use Illuminate\Console\Command;

/**
 * Scheduled every minute: escalates SLA-breached tickets across every workspace.
 */
class CheckTicketSla extends Command
{
    protected $signature = 'tickets:check-sla';

    protected $description = 'Escalate tickets that have breached their first-response SLA';

    public function handle(EscalationService $escalation): int
    {
        $total = 0;

        Tenant::query()->each(function (Tenant $tenant) use ($escalation, &$total): void {
            tenancy()->initialize($tenant);
            try {
                $total += $escalation->escalateBreached();
            } finally {
                tenancy()->end();
            }
        });

        $this->info("Escalated {$total} breached ticket(s).");

        return self::SUCCESS;
    }
}
