<?php

declare(strict_types=1);

namespace App\Modules\Leads\Services;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use Illuminate\Support\Facades\DB;

/**
 * Converts a qualified lead into a Contact (+ optional Deal) and records the
 * conversion on the lead. Idempotent: a already-converted lead is returned as-is.
 */
class LeadConversionService
{
    /**
     * @return array{contact: Contact, deal: ?Deal}
     */
    public function convert(Lead $lead, bool $createDeal = true): array
    {
        if ($lead->isConverted()) {
            $contact = $lead->contact;

            return ['contact' => $contact, 'deal' => null];
        }

        return DB::transaction(function () use ($lead, $createDeal): array {
            [$first, $last] = $this->splitName($lead->name);

            $contact = Contact::create([
                'first_name' => $first,
                'last_name' => $last,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'source' => $lead->source,
                'lifecycle_stage' => 'sql',
                'owner_id' => $lead->assigned_user_id,
            ]);

            $deal = null;
            if ($createDeal) {
                $pipeline = Pipeline::where('type', 'deal')->where('is_default', true)->first()
                    ?? Pipeline::where('type', 'deal')->first();

                if ($pipeline) {
                    $firstStage = $pipeline->stages()->orderBy('order')->first();
                    $deal = Deal::create([
                        'name' => $lead->name.' — Opportunity',
                        'pipeline_id' => $pipeline->id,
                        'stage_id' => $firstStage?->id,
                        'contact_id' => $contact->id,
                        'owner_id' => $lead->assigned_user_id,
                        'probability' => $firstStage?->probability,
                    ]);
                }
            }

            $lead->forceFill([
                'status' => 'qualified',
                'converted_to_contact_id' => $contact->id,
                'converted_at' => now(),
            ])->save();

            Activity::create([
                'type' => 'system',
                'subject_type' => Contact::class,
                'subject_id' => $contact->id,
                'body' => "Converted from lead \"{$lead->name}\".",
                'user_id' => $lead->assigned_user_id,
            ]);

            return ['contact' => $contact, 'deal' => $deal];
        });
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [$name];

        return [$parts[0], $parts[1] ?? ''];
    }
}
