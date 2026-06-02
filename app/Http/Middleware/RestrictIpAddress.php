<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks requests from IPs outside the tenant's allow-list. Runs after the
 * tenant is resolved; an empty allow-list permits everyone.
 */
class RestrictIpAddress
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if ($tenant !== null && ! $tenant->ipAllowed((string) $request->ip())) {
            abort(403, 'Your IP address is not permitted to access this workspace.');
        }

        return $next($request);
    }
}
