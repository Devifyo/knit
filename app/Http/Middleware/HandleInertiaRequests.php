<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version (busts the Inertia cache on deploy).
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props shared with every Inertia response.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'appName' => config('app.name'),

            'auth' => [
                'user' => $request->user() ? [
                    ...$request->user()->only('id', 'name', 'email'),
                    'roles' => $request->user()->getRoleNames(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                ] : null,
            ],

            // Current workspace (tenant) + white-label branding. Null on central
            // routes (signup/login).
            'tenant' => function () {
                $tenant = function_exists('tenant') ? tenant() : null;

                return $tenant ? [
                    'id' => $tenant->getTenantKey(),
                    'name' => $tenant->name,
                    'branding' => $tenant->branding(),
                ] : null;
            },

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'ai' => fn () => $request->session()->get('ai'),
            ],
        ];
    }
}
