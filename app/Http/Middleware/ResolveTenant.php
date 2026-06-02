<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Database\Models\Domain;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active workspace (tenant) for the request and makes RBAC
 * tenant-aware. Resolution order:
 *
 *   1. Custom domain  — full host matches a row in the `domains` table.
 *   2. Subdomain      — first host label matches a tenant `slug`
 *                       (e.g. acme.localhost -> slug "acme").
 *   3. Authenticated user fallback — the logged-in user's `tenant_id`.
 *      (Lets the app work on a single central host / bare IP, which can't carry
 *      tenant subdomains.)
 *
 * Once a tenant is active we pin spatie/laravel-permission's team id to it so
 * roles/permissions resolve within the correct workspace.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! tenant()) {
            $tenant = $this->resolveByHost($request->getHost())
                ?? $this->resolveByUser($request);

            if ($tenant) {
                tenancy()->initialize($tenant);
            }
        }

        if (tenant()) {
            app(PermissionRegistrar::class)->setPermissionsTeamId(tenant()->getTenantKey());

            // Drop any role/permission relations cached under a different team
            // so spatie re-resolves them for the active workspace.
            $request->user()?->unsetRelation('roles')->unsetRelation('permissions');
        }

        return $next($request);
    }

    protected function resolveByHost(string $host): ?Tenant
    {
        $central = config('tenancy.central_domains', []);

        if (in_array($host, $central, true)) {
            return null;
        }

        // Custom domain: exact host match.
        $domain = Domain::where('domain', $host)->first();
        if ($domain) {
            return Tenant::find($domain->tenant_id);
        }

        // Subdomain: <slug>.<central-domain>
        foreach ($central as $base) {
            if (str_ends_with($host, '.'.$base)) {
                $slug = substr($host, 0, -(strlen($base) + 1));

                return Tenant::where('slug', $slug)->first();
            }
        }

        return null;
    }

    protected function resolveByUser(Request $request): ?Tenant
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            return Tenant::find($user->tenant_id);
        }

        return null;
    }
}
