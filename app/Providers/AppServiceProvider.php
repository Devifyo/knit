<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LoginActivity;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Modules\Admin\Services\Rbac;
use App\Modules\Admin\Services\TenantMail;
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
use Stancl\Tenancy\Events\TenancyInitialized;

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

        // Apply each workspace's own SMTP (or restore env defaults) whenever a
        // tenant context is entered — covers web requests and queued mail jobs.
        Event::listen(TenancyInitialized::class, function (): void {
            app(TenantMail::class)->apply(tenant());
        });

        // Automation triggers — fire workflows on key record events. The engine
        // is tenant-guarded and a no-op when no workflow listens.
        $engine = fn () => app(WorkflowEngine::class);
        Lead::created(fn ($m) => $engine()->trigger('lead.created', $m));
        Contact::created(fn ($m) => $engine()->trigger('contact.created', $m));
        Deal::created(fn ($m) => $engine()->trigger('deal.created', $m));

        // Integration triggers — fan record lifecycle + key domain events out to
        // tenant webhook endpoints (Zapier-compatible). Tenant-guarded + no-op
        // when no endpoint subscribes, so it's free when unused.
        $hooks = fn () => app(WebhookDispatcher::class);

        // Contacts
        Contact::created(fn (Contact $m) => $hooks()->dispatch('contact.created', $m->only(['id', 'first_name', 'last_name', 'email'])));
        Contact::updated(fn (Contact $m) => $hooks()->dispatch('contact.updated', $m->only(['id', 'first_name', 'last_name', 'email'])));
        Contact::deleted(fn (Contact $m) => $hooks()->dispatch('contact.deleted', $m->only(['id'])));

        // Companies
        Company::created(fn (Company $m) => $hooks()->dispatch('company.created', $m->only(['id', 'name'])));
        Company::updated(fn (Company $m) => $hooks()->dispatch('company.updated', $m->only(['id', 'name'])));

        // Leads (+ conversion)
        Lead::created(fn (Lead $m) => $hooks()->dispatch('lead.created', $m->only(['id', 'name', 'email', 'status'])));
        Lead::updated(function (Lead $m) use ($hooks): void {
            $hooks()->dispatch('lead.updated', $m->only(['id', 'name', 'email', 'status']));
            if ($m->wasChanged('converted_at') && $m->converted_at !== null) {
                $hooks()->dispatch('lead.converted', $m->only(['id', 'name', 'converted_to_contact_id']));
            }
        });

        // Deals (+ won/lost)
        Deal::created(fn (Deal $m) => $hooks()->dispatch('deal.created', $m->only(['id', 'name', 'amount', 'status'])));
        Deal::updated(function (Deal $m) use ($hooks): void {
            $hooks()->dispatch('deal.updated', $m->only(['id', 'name', 'amount', 'status']));
            if ($m->wasChanged('status') && $m->status === 'won') {
                $hooks()->dispatch('deal.won', $m->only(['id', 'name', 'amount']));
            }
            if ($m->wasChanged('status') && $m->status === 'lost') {
                $hooks()->dispatch('deal.lost', $m->only(['id', 'name', 'amount']));
            }
        });

        // CPQ + billing
        Quote::updated(function (Quote $m) use ($hooks): void {
            if ($m->wasChanged('status') && $m->status === 'accepted') {
                $hooks()->dispatch('quote.accepted', $m->only(['id', 'number']));
            }
        });
        Invoice::updated(function (Invoice $m) use ($hooks): void {
            if ($m->wasChanged('status') && $m->status === 'paid') {
                $hooks()->dispatch('invoice.paid', $m->only(['id', 'number', 'total_minor']));
            }
        });

        // Support, productivity
        Ticket::created(fn (Ticket $m) => $hooks()->dispatch('ticket.created', $m->only(['id', 'subject', 'status'])));
        Task::updated(function (Task $m) use ($hooks): void {
            if ($m->wasChanged('completed_at') && $m->completed_at !== null) {
                $hooks()->dispatch('task.completed', $m->only(['id', 'title']));
            }
        });
        Project::created(fn (Project $m) => $hooks()->dispatch('project.created', $m->only(['id', 'name'])));

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
