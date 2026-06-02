<?php

declare(strict_types=1);

use App\Models\Quote;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Modules\Deals\Services\PricingService;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function cpqWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Ada', 'email' => 'o@acme.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

function quoteWithItems(): Quote
{
    $quote = Quote::create(['number' => 'Q-1', 'currency' => 'EUR', 'tax_rate' => 20]);
    $quote->items()->create(['name' => 'Plan A', 'quantity' => 2, 'unit_price' => 10000, 'discount_pct' => 10, 'position' => 0]);
    $quote->items()->create(['name' => 'Setup', 'quantity' => 1, 'unit_price' => 5000, 'discount_pct' => 0, 'position' => 1]);

    return $quote->fresh('items');
}

it('computes quote totals with per-line discount, tax and currency', function () {
    cpqWorkspace();
    $totals = app(PricingService::class)->totals(quoteWithItems());

    // (2 * 100.00 - 10%) + 50.00 = 230.00; +20% tax = 46.00; total 276.00
    expect($totals['subtotal'])->toBe(23000)
        ->and($totals['tax'])->toBe(4600)
        ->and($totals['total'])->toBe(27600)
        ->and($totals['total_formatted'])->toBe('€276.00');
});

it('renders a quote to a PDF with the right content type', function () {
    [, $owner] = cpqWorkspace();
    $quote = quoteWithItems();

    $response = $this->actingAs($owner)->get("/quotes/{$quote->id}/pdf");

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('adds items through the controller and recomputes the total', function () {
    [, $owner] = cpqWorkspace();
    $quote = Quote::create(['number' => 'Q-2', 'currency' => 'USD', 'tax_rate' => 0]);

    $this->actingAs($owner)
        ->post("/quotes/{$quote->id}/items", ['name' => 'License', 'quantity' => 3, 'unit_price' => 99.99, 'discount_pct' => 0])
        ->assertRedirect();

    $totals = app(PricingService::class)->totals($quote->fresh('items'));
    expect($totals['subtotal'])->toBe(29997); // 3 * 9999 minor units
});
