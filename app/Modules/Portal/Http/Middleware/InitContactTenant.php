<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * For an authenticated portal contact, initialize their tenant context (so the
 * global TenantScope filters correctly + branding resolves) and share the contact
 * with every Inertia portal page. Runs after the `auth:contact` guard.
 */
class InitContactTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $contact = Auth::guard('contact')->user();

        if ($contact) {
            $tenant = Tenant::find($contact->tenant_id);
            if ($tenant) {
                tenancy()->initialize($tenant);
            }

            Inertia::share('portalAuth', [
                'contact' => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                ],
                // True when a staff member is viewing the portal as this contact.
                'impersonating' => $request->session()->has('impersonator_user_id'),
            ]);
        }

        return $next($request);
    }
}
