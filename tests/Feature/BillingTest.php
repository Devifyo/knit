<?php

declare(strict_types=1);

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\Rbac;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Billing\Services\Entitlements;
use Database\Seeders\BillingSeeder;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function billingWorkspace(string $name = 'Acme', string $email = 'o@acme.test'): array
{
    (new BillingSeeder)->run();
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => $name, 'owner_name' => $name.' Owner', 'email' => $email, 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('subscribes a tenant to a plan and settles the invoice through the gateway', function () {
    [, $owner] = billingWorkspace();

    $this->actingAs($owner)->post('/settings/billing/subscribe', ['plan' => 'pro'])
        ->assertRedirect();

    $sub = app(BillingService::class)->current();
    expect($sub->status)->toBe('active')
        ->and($sub->plan->key)->toBe('pro');

    $invoice = Invoice::first();
    expect($invoice->status)->toBe('paid')
        ->and($invoice->total_minor)->toBe(4900)
        ->and($invoice->payments()->where('status', 'succeeded')->exists())->toBeTrue();
});

it('applies a coupon discount to the invoice total', function () {
    [, $owner] = billingWorkspace();

    $this->actingAs($owner)->post('/settings/billing/subscribe', ['plan' => 'pro', 'coupon' => 'LAUNCH25'])
        ->assertRedirect();

    $invoice = Invoice::first();
    // 25% off 4900 = 1225 discount → 3675 total.
    expect($invoice->discount_minor)->toBe(1225)
        ->and($invoice->total_minor)->toBe(3675)
        ->and(Coupon::where('code', 'LAUNCH25')->first()->redeemed_count)->toBe(1);
});

it('rejects an invalid coupon', function () {
    [, $owner] = billingWorkspace();

    $this->actingAs($owner)->post('/settings/billing/subscribe', ['plan' => 'pro', 'coupon' => 'NOPE'])
        ->assertSessionHasErrors('coupon');

    expect(Invoice::count())->toBe(0);
});

it('starts a trial with a future end date', function () {
    [, $owner] = billingWorkspace();
    $pro = Plan::where('key', 'pro')->first();

    $sub = app(BillingService::class)->startTrial($pro);

    expect($sub->status)->toBe('trialing')
        ->and($sub->onTrial())->toBeTrue()
        ->and($sub->trial_ends_at->isFuture())->toBeTrue();
});

it('enforces the plan seat limit when inviting members', function () {
    [$tenant, $owner] = billingWorkspace();
    // Free plan = 3 seats. Subscribe, then fill the remaining seats.
    app(BillingService::class)->subscribe(Plan::where('key', 'free')->first());
    User::factory()->count(2)->create(['tenant_id' => $tenant->getTenantKey()]);

    expect(app(Entitlements::class)->seatsUsed())->toBe(3)
        ->and(app(Entitlements::class)->canAddSeat())->toBeFalse();

    $this->actingAs($owner)->post('/members/invite', ['email' => 'new@acme.test', 'role' => Rbac::AGENT])
        ->assertSessionHasErrors('email');
});

it('downloads an invoice as a PDF', function () {
    [, $owner] = billingWorkspace();
    app(BillingService::class)->subscribe(Plan::where('key', 'pro')->first());
    $invoice = Invoice::first();

    $res = $this->actingAs($owner)->get("/settings/billing/invoices/{$invoice->id}/pdf");
    $res->assertOk();
    expect($res->headers->get('content-type'))->toContain('application/pdf');
});

it('isolates invoices between tenants', function () {
    [, $acmeOwner] = billingWorkspace('Acme', 'o@acme.test');
    app(BillingService::class)->subscribe(Plan::where('key', 'pro')->first());
    tenancy()->end();

    [, $globexOwner] = billingWorkspace('Globex', 'o@globex.test');
    app(BillingService::class)->subscribe(Plan::where('key', 'pro')->first());
    $globexInvoice = Invoice::first();
    tenancy()->end();

    $this->actingAs($acmeOwner)->get("/settings/billing/invoices/{$globexInvoice->id}/pdf")->assertNotFound();
});
