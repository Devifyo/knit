<?php

declare(strict_types=1);

use App\Modules\Admin\Services\WorkspaceProvisioner;
use Spatie\Permission\PermissionRegistrar;

/** Spin up a tenant + owner and enter its context. */
function reportsWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Reportco', 'owner_name' => 'Report Owner', 'email' => 'o@reportco.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('builds a report for every supported entity type', function () {
    [, $owner] = reportsWorkspace();

    $expected = [
        'deals' => 'Deals report',
        'leads' => 'Leads report',
        'contacts' => 'Contacts report',
        'companies' => 'Companies report',
        'tickets' => 'Tickets report',
        'campaigns' => 'Campaigns report',
    ];

    foreach ($expected as $entity => $title) {
        $this->actingAs($owner)->get("/reports?entity={$entity}")
            ->assertOk()
            ->assertInertia(fn ($p) => $p->component('Reports/Index', false)->where('report.title', $title));
    }
});

it('exports an extended report as CSV', function () {
    [, $owner] = reportsWorkspace();

    $this->actingAs($owner)->get('/reports/export/csv?entity=tickets')
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
