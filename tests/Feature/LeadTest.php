<?php

declare(strict_types=1);

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Services\AI\GeminiService;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function leadWorkspace(string $name = 'Acme', string $email = 'o@acme.test'): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => $name, 'owner_name' => $name.' Owner', 'email' => $email, 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('scores a captured lead from signals instead of a flat default', function () {
    [, $owner] = leadWorkspace();

    $this->actingAs($owner)->post('/leads', ['name' => 'Dana', 'email' => 'dana@bigcorp.io', 'phone' => '555-2020'])
        ->assertRedirect();

    $lead = Lead::first();
    // Work email + phone → well above the old flat 40, and never 0.
    expect($lead->score)->toBeGreaterThan(40)
        ->and($lead->score)->toBeLessThanOrEqual(100);
});

it('scores work emails higher than free-provider emails', function () {
    $ai = app(GeminiService::class);
    $work = $ai->heuristicLeadScore(['email' => 'cto@enterprise.com', 'phone' => '555']);
    $free = $ai->heuristicLeadScore(['email' => 'someone@gmail.com']);

    expect($work)->toBeGreaterThan($free)
        ->and($free)->toBeGreaterThan(0);
});

it('records the capture URL a public lead came from', function () {
    [$tenant] = leadWorkspace();
    tenancy()->end();

    $this->post("/f/{$tenant->slug}", ['name' => 'Web Visitor', 'email' => 'web@acme-prospect.com'])
        ->assertRedirect();

    tenancy()->initialize($tenant);
    $lead = Lead::where('email', 'web@acme-prospect.com')->first();
    expect($lead)->not->toBeNull()
        ->and($lead->source)->toBe('Capture form')
        ->and($lead->source_url)->toContain('/f/'.$tenant->slug)
        ->and($lead->score)->toBeGreaterThan(0);
    tenancy()->end();
});

it('shows a lead detail page', function () {
    [, $owner] = leadWorkspace();
    $lead = Lead::factory()->create(['name' => 'Jordan Lead', 'source' => 'Capture form', 'source_url' => 'https://x.test/f/acme', 'assigned_user_id' => $owner->id]);

    $this->actingAs($owner)->get("/leads/{$lead->id}")
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Leads/Show', false)
            ->where('lead.name', 'Jordan Lead')
            ->where('lead.source_url', 'https://x.test/f/acme'));
});

it('isolates lead detail across tenants', function () {
    [, $acmeOwner] = leadWorkspace('Acme', 'o@acme.test');
    tenancy()->end();
    [, $globexOwner] = leadWorkspace('Globex', 'o@globex.test');
    $globexLead = Lead::factory()->create(['assigned_user_id' => $globexOwner->id]);
    tenancy()->end();

    $this->actingAs($acmeOwner)->get("/leads/{$globexLead->id}")->assertNotFound();
});
