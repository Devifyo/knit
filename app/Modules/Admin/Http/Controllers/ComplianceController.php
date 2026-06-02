<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * GDPR data-subject tooling: export a contact's data (portability) and erase it
 * (right to be forgotten). Both are tenant-scoped via route binding and audited.
 */
class ComplianceController extends Controller
{
    /** Right to data portability — download everything held about a contact. */
    public function export(Contact $contact): JsonResponse
    {
        $contact->load(['activities', 'company:id,name']);

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'contact' => $contact->only(['id', 'first_name', 'last_name', 'email', 'phone', 'job_title', 'lifecycle_stage', 'source', 'social_profiles', 'custom_fields', 'created_at']),
            'company' => $contact->company?->only(['id', 'name']),
            'activities' => $contact->activities->map(fn ($a) => $a->only(['id', 'type', 'body', 'created_at'])),
        ];

        return response()->json($payload, 200, [
            'Content-Disposition' => 'attachment; filename="contact-'.$contact->id.'-export.json"',
        ], JSON_PRETTY_PRINT);
    }

    /** Right to erasure — anonymize PII in place, keeping the audited record. */
    public function erase(Contact $contact): RedirectResponse
    {
        $contact->update([
            'first_name' => 'Redacted',
            'last_name' => 'Contact',
            'email' => null,
            'phone' => null,
            'social_profiles' => [],
            'custom_fields' => [],
            'anonymized_at' => now(),
        ]);

        return back()->with('success', "Contact #{$contact->id} has been anonymized (GDPR erasure).");
    }
}
