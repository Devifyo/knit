<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Portal/Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Email is unique per tenant but may recur across tenants, so check every
        // portal-enabled contact with this email and match the password.
        $candidates = Contact::withoutGlobalScopes()
            ->where('email', $data['email'])
            ->where('portal_enabled', true)
            ->whereNotNull('portal_activated_at')
            ->get();

        $contact = $candidates->first(fn (Contact $c) => Hash::check($data['password'], (string) $c->password));

        if (! $contact) {
            throw ValidationException::withMessages(['email' => 'Those credentials do not match our records.']);
        }

        Auth::guard('contact')->login($contact, (bool) $request->boolean('remember'));
        $request->session()->regenerate();
        $contact->forceFill(['portal_last_login_at' => now()])->saveQuietly();

        return redirect()->intended('/portal');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('contact')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/portal/login');
    }

    /**
     * End a staff impersonation session: log out the contact guard only (the
     * staff web session is untouched) and return to the contact's admin page.
     */
    public function stopImpersonating(Request $request): RedirectResponse
    {
        abort_unless($request->session()->has('impersonator_user_id'), 403);

        $return = $request->session()->pull('impersonate_return', '/contacts');
        $request->session()->forget('impersonator_user_id');
        Auth::guard('contact')->logout();

        return redirect($return);
    }
}
