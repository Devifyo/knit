<?php

declare(strict_types=1);

namespace App\Modules\Leads\Jobs;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Leads\Mail\LeadReceivedMail;
use App\Modules\Leads\Mail\NewLeadMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\PermissionRegistrar;

/**
 * Fired when a lead is submitted through a public form: emails the submitter a
 * confirmation, and alerts every workspace member who has the `leads.notify`
 * permission. Tenant-aware + queued so the public form never blocks on mail.
 */
class NotifyNewLeadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public string $tenantId, public int $leadId) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (! $tenant) {
            return;
        }
        $previous = tenant();
        tenancy()->initialize($tenant);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

        try {
            $lead = Lead::find($this->leadId);
            if (! $lead) {
                return;
            }
            $workspace = (string) ($tenant->name ?? 'our team');

            // 1) Confirmation to the person who submitted the lead.
            if ($lead->email) {
                $this->silently(fn () => Mail::to($lead->email)->send(new LeadReceivedMail($lead->name, $workspace)));
            }

            // 2) Alert members who opted into lead notifications.
            $recipients = User::all()->filter(fn (User $u) => $u->can('leads.notify'));
            foreach ($recipients as $member) {
                $this->silently(fn () => Mail::to($member->email)->send(new NewLeadMail($lead, $workspace)));
            }
        } finally {
            if ($previous !== null) {
                tenancy()->initialize($previous);
            } else {
                tenancy()->end();
            }
        }
    }

    private function silently(callable $send): void
    {
        try {
            $send();
        } catch (\Throwable) {
            // Best-effort: a mail misconfig must never fail lead capture.
        }
    }
}
