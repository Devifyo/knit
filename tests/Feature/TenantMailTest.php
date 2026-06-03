<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\TenantMail;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function mailWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Acme Owner', 'email' => 'o@acme.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('uses the workspace SMTP when configured and falls back to env otherwise', function () {
    [$tenant] = mailWorkspace();
    $envDefault = config('mail.default');

    // Falls back while unconfigured.
    app(TenantMail::class)->apply($tenant);
    expect(config('mail.default'))->toBe($envDefault);

    // Override once the workspace sets its own SMTP.
    $tenant->smtp_host = 'smtp.acme.test';
    $tenant->smtp_port = 2525;
    $tenant->smtp_username = 'apikey';
    $tenant->smtp_password = 'secret';
    $tenant->smtp_encryption = 'tls';
    $tenant->smtp_from_address = 'hello@acme.test';
    $tenant->save();

    app(TenantMail::class)->apply($tenant->fresh());
    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.acme.test')
        ->and(config('mail.mailers.smtp.port'))->toBe(2525)
        ->and(config('mail.from.address'))->toBe('hello@acme.test');

    // Clearing the host reverts to the env default.
    $tenant->smtp_host = null;
    $tenant->save();
    app(TenantMail::class)->apply($tenant->fresh());
    expect(config('mail.default'))->toBe($envDefault);
});

it('stores the SMTP password encrypted', function () {
    [$tenant] = mailWorkspace();
    $tenant->smtp_host = 'smtp.acme.test';
    $tenant->smtp_password = 'super-secret';
    $tenant->save();

    // The cast decrypts on read…
    expect($tenant->fresh()->smtp_password)->toBe('super-secret');
    // …but the raw stored value is not plaintext.
    $raw = json_encode($tenant->fresh()->getAttributes());
    expect($raw)->not->toContain('super-secret');
});

it('lets an admin save workspace email settings', function () {
    [, $owner] = mailWorkspace();

    $this->actingAs($owner)->put('/settings/email', [
        'host' => 'smtp.acme.test',
        'port' => 587,
        'username' => 'apikey',
        'password' => 'pw',
        'encryption' => 'tls',
        'from_address' => 'hello@acme.test',
        'from_name' => 'Acme',
    ])->assertRedirect();

    expect(tenant()->fresh()->hasCustomMail())->toBeTrue();
});

it('shows mail_configured in shared props', function () {
    [, $owner] = mailWorkspace();

    $this->actingAs($owner)->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->where('tenant.mail_configured', false));
});
