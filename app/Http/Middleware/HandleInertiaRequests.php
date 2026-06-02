<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Subscription;
use App\Modules\Industry\Services\Modules;
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

            // Lightweight billing banner state (trial countdown / current plan).
            // Only resolved when a tenant + user are present.
            'billing' => function () use ($request) {
                if (! $request->user() || ! function_exists('tenant') || tenant() === null) {
                    return null;
                }
                $sub = Subscription::with('plan')->latest()->first();

                return [
                    'plan' => $sub?->plan?->name,
                    'on_trial' => (bool) $sub?->onTrial(),
                    'trial_ends_at' => $sub?->trial_ends_at?->toFormattedDateString(),
                ];
            },

            // Sidebar entries for the tenant's enabled industry modules.
            'industryNav' => function () use ($request) {
                if (! $request->user() || ! function_exists('tenant') || tenant() === null) {
                    return [];
                }

                return app(Modules::class)->navEntries();
            },
        ];
    }
}
