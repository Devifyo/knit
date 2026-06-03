<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Tenant;
use App\Modules\Portal\Mail\PortalAccessMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Self-service portal password reset. Emails a fresh set-password link to every
 * portal-enabled contact with the given email (the link reuses the activation
 * flow). Always responds generically to avoid email enumeration.
 */
class ForgotPasswordController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Portal/Auth/ForgotPassword');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        $contacts = Contact::withoutGlobalScopes()
            ->where('email', $data['email'])
            ->where('portal_enabled', true)
            ->get();

        foreach ($contacts as $contact) {
            $contact->forceFill(['portal_token' => Str::random(48)])->save();
            $workspace = optional(Tenant::find($contact->tenant_id))->name ?? 'your workspace';

            try {
                Mail::to($contact->email)->send(new PortalAccessMail(
                    url('/portal/activate/'.$contact->portal_token),
                    $workspace,
                    reset: true,
                ));
            } catch (\Throwable) {
                // Swallow delivery errors — never reveal whether the email exists.
            }
        }

        return back()->with('success', "If an account exists for {$data['email']}, we've emailed a reset link.");
    }
}
