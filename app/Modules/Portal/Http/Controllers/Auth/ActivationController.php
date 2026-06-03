<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Set-password flow. Staff "invite to portal" generates a one-time token; the
 * contact follows the link to choose a password and activate their account.
 */
class ActivationController extends Controller
{
    public function show(string $token): Response
    {
        $contact = $this->resolve($token);
        $tenant = Tenant::find($contact->tenant_id);

        return Inertia::render('Portal/Auth/Activate', [
            'token' => $token,
            'email' => $contact->email,
            'name' => $contact->name,
            'workspace' => $tenant ? ['name' => $tenant->name, 'brand_color' => $tenant->brand_color] : null,
        ]);
    }

    public function activate(Request $request, string $token): RedirectResponse
    {
        $contact = $this->resolve($token);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $contact->forceFill([
            'password' => $request->string('password')->value(),
            'portal_activated_at' => now(),
            'portal_token' => null,
        ])->save();

        Auth::guard('contact')->login($contact);
        $request->session()->regenerate();

        return redirect('/portal');
    }

    /** Resolve a contact by activation token (across tenants), 404 if invalid. */
    private function resolve(string $token): Contact
    {
        return Contact::withoutGlobalScopes()
            ->where('portal_token', $token)
            ->where('portal_enabled', true)
            ->firstOrFail();
    }
}
