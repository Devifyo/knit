<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Jobs;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Sends a campaign to its audience: creates a tracked CampaignRecipient per
 * person (open pixel + click-wrapped CTA), splits A/B variants, and emails them.
 * Tenant-aware and idempotent (skips a campaign already sent).
 */
class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $tenantId, public int $campaignId) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (! $tenant) {
            return;
        }
        tenancy()->initialize($tenant);

        try {
            $campaign = Campaign::find($this->campaignId);
            if (! $campaign || $campaign->status === 'sent') {
                return;
            }
            $campaign->update(['status' => 'sending']);

            $audience = $campaign->audience === 'leads'
                ? Lead::whereNotNull('email')->get(['id', 'name', 'email'])->map(fn (Lead $l) => ['id' => null, 'name' => $l->name, 'email' => $l->email])
                : Contact::whereNotNull('email')->get()->map(fn (Contact $c) => ['id' => $c->id, 'name' => $c->name, 'email' => $c->email]);

            foreach ($audience->values() as $i => $person) {
                $variant = $campaign->isAbTest() ? ($i % 2 === 0 ? 'A' : 'B') : 'A';
                $token = Str::random(40);

                $campaign->recipients()->create([
                    'contact_id' => $person['id'],
                    'email' => $person['email'],
                    'name' => $person['name'],
                    'variant' => $variant,
                    'token' => $token,
                    'sent_at' => now(),
                ]);

                $subject = $variant === 'B' && $campaign->subject_b ? $campaign->subject_b : $campaign->subject;
                Mail::html($this->renderBody($campaign, $token), fn ($m) => $m->to($person['email'])->subject($subject));
            }

            $campaign->update(['status' => 'sent', 'sent_at' => now()]);
        } finally {
            tenancy()->end();
        }
    }

    protected function renderBody(Campaign $campaign, string $token): string
    {
        $body = $campaign->body;

        if ($campaign->cta_url) {
            $tracked = url('/track/click/'.$token).'?url='.urlencode($campaign->cta_url);
            $body .= '<p><a href="'.$tracked.'">'.e($campaign->cta_label ?: 'Learn more').'</a></p>';
        }

        // 1x1 open-tracking pixel.
        $body .= '<img src="'.url('/track/open/'.$token).'" width="1" height="1" alt="" />';

        return $body;
    }
}
