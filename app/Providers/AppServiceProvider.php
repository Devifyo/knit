<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Admin\Services\Rbac;
use App\Services\AI\GeminiService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Single entry point for all AI calls (see docs/ARCHITECTURE.md §AI).
        $this->app->singleton(GeminiService::class, function ($app) {
            $config = $app['config']['services.gemini'];

            return new GeminiService(
                enabled: (bool) ($config['enabled'] ?? false),
                apiKey: $config['key'] ?? null,
                model: $config['model'] ?? 'gemini-2.0-flash',
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Owners bypass every permission check within their workspace.
        Gate::before(fn ($user) => $user->hasRole(Rbac::OWNER) ? true : null);
    }
}
