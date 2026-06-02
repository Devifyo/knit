<?php

declare(strict_types=1);

use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Modules\Analytics\Events\DashboardStatsUpdated;
use App\Modules\Analytics\Services\ReportService;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function analyticsWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Ada', 'email' => 'o@acme.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('filters a report by owner', function () {
    [$tenant, $owner] = analyticsWorkspace();
    $other = User::factory()->create(['tenant_id' => $tenant->id]);
    $pipeline = Pipeline::where('is_default', true)->first();
    $stage = $pipeline->stages()->first()->id;

    Deal::factory()->count(2)->create(['pipeline_id' => $pipeline->id, 'stage_id' => $stage, 'owner_id' => $owner->id]);
    Deal::factory()->count(3)->create(['pipeline_id' => $pipeline->id, 'stage_id' => $stage, 'owner_id' => $other->id]);

    $report = app(ReportService::class)->build(['entity' => 'deals', 'owner_id' => $owner->id]);

    expect($report['rows'])->toHaveCount(2);
});

it('exports a report as CSV, Excel and PDF', function () {
    [$tenant, $owner] = analyticsWorkspace();
    $pipeline = Pipeline::where('is_default', true)->first();
    Deal::factory()->count(2)->create(['pipeline_id' => $pipeline->id, 'stage_id' => $pipeline->stages()->first()->id, 'owner_id' => $owner->id]);

    $csv = $this->actingAs($owner)->get('/reports/export/csv');
    $csv->assertOk();
    expect($csv->headers->get('content-type'))->toContain('text/csv');

    $this->actingAs($owner)->get('/reports/export/xlsx')->assertOk();

    $pdf = $this->actingAs($owner)->get('/reports/export/pdf');
    $pdf->assertOk();
    expect($pdf->headers->get('content-type'))->toContain('application/pdf');
});

it('reflects a closed deal on the dashboard and broadcasts the update', function () {
    Event::fake([DashboardStatsUpdated::class]);
    [$tenant, $owner] = analyticsWorkspace();
    $pipeline = Pipeline::where('is_default', true)->first();
    $newStage = $pipeline->stages()->where('type', 'open')->orderBy('order')->first();
    $wonStage = $pipeline->stages()->where('type', 'won')->first();

    $deal = Deal::factory()->create([
        'pipeline_id' => $pipeline->id, 'stage_id' => $newStage->id, 'owner_id' => $owner->id,
        'amount' => 500000, 'status' => 'open',
    ]);

    // Close it (move to the Won stage).
    $this->actingAs($owner)->patch("/deals/{$deal->id}/move", ['stage_id' => $wonStage->id])->assertRedirect();

    expect($deal->fresh()->status)->toBe('won');
    Event::assertDispatched(DashboardStatsUpdated::class);

    // The dashboard "won this month" widget now reflects it.
    $this->actingAs($owner)->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Dashboard', false)->where('won.count', 1));
});
