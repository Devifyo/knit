<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When a workspace requires 2FA, users who haven't confirmed it are funnelled to
 * the Security settings page to enrol. The enrolment + logout routes stay
 * reachable so they can actually set it up (no redirect loop).
 */
class EnforceTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();
        $user = $request->user();

        if ($tenant === null || $user === null || ! $tenant->require_2fa) {
            return $next($request);
        }

        // Already enrolled, or on a route needed to enrol / sign out.
        if ($user->two_factor_confirmed_at !== null || $this->isAllowed($request)) {
            return $next($request);
        }

        return redirect('/settings/security')
            ->with('error', 'Your workspace requires two-factor authentication. Please enable it to continue.');
    }

    private function isAllowed(Request $request): bool
    {
        return $request->is('settings/security')
            || $request->is('user/two-factor-*')
            || $request->is('user/confirmed-two-factor-authentication')
            || $request->is('user/confirm-password*')
            || $request->is('logout');
    }
}
