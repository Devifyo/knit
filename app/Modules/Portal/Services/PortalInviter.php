<?php

declare(strict_types=1);

namespace App\Modules\Portal\Services;

use App\Models\Contact;
use App\Modules\Portal\Mail\PortalAccessMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Enables customer-portal access for a contact and emails them a set-password
 * link. Used both by the manual "Invite to portal" action and automatically on
 * lead → contact conversion. Safe to call when mail isn't configured (the send
 * is best-effort; the activation link still lives on the contact).
 */
class PortalInviter
{
    /**
     * @return bool whether an invite email was sent
     */
    public function invite(Contact $contact, bool $reset = false): bool
    {
        if (! $contact->email) {
            return false;
        }

        $contact->portal_enabled = true;
        if ($reset || $contact->portal_activated_at === null) {
            $contact->portal_token = Str::random(48);
            if ($reset) {
                $contact->portal_activated_at = null;
            }
        }
        $contact->save();

        // Already active and not resetting — nothing to send.
        if (! $contact->portal_token) {
            return false;
        }

        try {
            Mail::to($contact->email)->send(new PortalAccessMail(
                url('/portal/activate/'.$contact->portal_token),
                (string) (tenant('name') ?? 'your workspace'),
                reset: $reset,
            ));

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
