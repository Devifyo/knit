<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LoginActivity;
use App\Models\User;
use App\Modules\Admin\Services\Rbac;
use App\Modules\Automation\Services\WorkflowEngine;
use App\Modules\Billing\Contracts\PaymentGateway;
use App\Modules\Billing\Gateways\ManualPaymentGateway;
use App\Modules\Billing\Gateways\StripePaymentGateway;
use App\Modules\Integrations\Services\WebhookDispatcher;
use App\Services\AI\GeminiService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
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

        // Payment provider — swap drivers via BILLING_GATEWAY without touching
        // the billing flow. The manual driver keeps billing fully functional
        // with no external provider configured.
        $this->app->bind(PaymentGateway::class, function ($app): PaymentGateway {
            $config = $app['config']['services.billing'] ?? [];

            return match ($config['gateway'] ?? 'manual') {
                'stripe' => new StripePaymentGateway($config['stripe_secret'] ?? null),
                default => new ManualPaymentGateway,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Owners bypass every permission check within their workspace.
        Gate::before(fn ($user) => $user->hasRole(Rbac::OWNER) ? true : null);

        // Automation triggers — fire workflows on key record events. The engine
        // is tenant-guarded and a no-op when no workflow listens.
        $engine = fn () => app(WorkflowEngine::class);
        Lead::created(fn ($m) => $engine()->trigger('lead.created', $m));
        Contact::created(fn ($m) => $engine()->trigger('contact.created', $m));
        Deal::created(fn ($m) => $engine()->trigger('deal.created', $m));

        // Integration triggers — fan the same record events out to tenant
        // webhook endpoints (Zapier-compatible). Tenant-guarded + no-op when no
        // endpoint subscribes, so it's free when unused.
        $hooks = fn () => app(WebhookDispatcher::class);
        Lead::created(fn (Lead $m) => $hooks()->dispatch('lead.created', $m->only(['id', 'name', 'email', 'status'])));
        Contact::created(fn (Contact $m) => $hooks()->dispatch('contact.created', $m->only(['id', 'first_name', 'last_name', 'email'])));
        Deal::created(fn (Deal $m) => $hooks()->dispatch('deal.created', $m->only(['id', 'name', 'amount', 'status'])));

        // Device/session tracking — record each successful login.
        Event::listen(Login::class, function (Login $event): void {
            $user = $event->user;
            if (! $user instanceof User) {
                return;
            }
            LoginActivity::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 1023),
                'logged_in_at' => now(),
            ]);
        });
    }
}
