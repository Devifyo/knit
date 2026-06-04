<?php

declare(strict_types=1);

namespace App\Modules\Leads\Jobs;

use App\Models\Lead;
use App\Models\Tenant;
use App\Services\AI\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Scores a freshly-captured lead. Runs through GeminiService (real AI when the
 * workspace has it enabled, otherwise a transparent heuristic), so every lead is
 * scored from the moment it's created — no flat default. Tenant-aware + queued so
 * public capture forms never block on the model.
 */
class ScoreLeadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public string $tenantId, public int $leadId) {}

    public function handle(GeminiService $ai): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (! $tenant) {
            return;
        }
        $previous = tenant();
        tenancy()->initialize($tenant);

        try {
            $lead = Lead::find($this->leadId);
            if (! $lead) {
                return;
            }

            $result = $ai->scoreLead($lead);
            $lead->forceFill([
                'score' => $result['score'],
                'custom_fields' => [...($lead->custom_fields ?? []), 'ai_reasons' => $result['reasons']],
            ])->save();
        } finally {
            if ($previous !== null) {
                tenancy()->initialize($previous);
            } else {
                tenancy()->end();
            }
        }
    }
}
