<?php

declare(strict_types=1);

namespace App\Modules\Admin\Services;

use App\Models\Tenant;

/**
 * Applies a workspace's own SMTP settings to the live mail config when present,
 * and otherwise restores the environment (.env) defaults. Driven by the
 * TenancyInitialized event, so it covers both web requests and queued jobs
 * (campaigns, workflow emails, portal invites) — each of which initializes its
 * tenant before sending.
 */
class TenantMail
{
    /**
     * The env-based mail config, snapshotted once before any tenant override.
     *
     * @var array<string, mixed>|null
     */
    private static ?array $defaults = null;

    /** Config keys this service swaps in/out. */
    private const KEYS = [
        'mail.default',
        'mail.from.address',
        'mail.from.name',
        'mail.mailers.smtp.host',
        'mail.mailers.smtp.port',
        'mail.mailers.smtp.username',
        'mail.mailers.smtp.password',
        'mail.mailers.smtp.encryption',
    ];

    public function apply(?Tenant $tenant): void
    {
        // Snapshot the env defaults the first time we run (config:cache-safe).
        self::$defaults ??= collect(self::KEYS)->mapWithKeys(fn (string $k) => [$k => config($k)])->all();

        if ($tenant && $tenant->hasCustomMail()) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $tenant->smtp_host,
                'mail.mailers.smtp.port' => (int) ($tenant->smtp_port ?: 587),
                'mail.mailers.smtp.username' => $tenant->smtp_username,
                'mail.mailers.smtp.password' => $tenant->smtp_password,
                'mail.mailers.smtp.encryption' => $tenant->smtp_encryption ?: null,
                'mail.from.address' => $tenant->smtp_from_address ?: self::$defaults['mail.from.address'],
                'mail.from.name' => $tenant->smtp_from_name ?: $tenant->name,
            ]);
        } else {
            // Restore the env defaults (clears any previous tenant's override).
            config(self::$defaults);
        }

        // Rebuild the resolved mailer so the next send picks up the new config.
        // (Wrapped because Mail::fake() swaps in a manager without forgetMailers.)
        try {
            app('mail.manager')->forgetMailers();
        } catch (\Throwable) {
            // No-op under the test mail fake.
        }
    }
}
