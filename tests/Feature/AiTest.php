<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Services\AI\GeminiService;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function aiWorkspace(): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => 'Acme', 'owner_name' => 'Ada', 'email' => 'o@acme.test', 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('updates a lead score from AI', function () {
    [, $owner] = aiWorkspace();
    $lead = Lead::factory()->create(['assigned_user_id' => $owner->id, 'score' => 0]);

    $this->mock(GeminiService::class, fn ($m) => $m->shouldReceive('scoreLead')
        ->once()->andReturn(['score' => 87, 'reasons' => ['Has a work email', 'Enterprise source']]));

    $this->actingAs($owner)->post("/leads/{$lead->id}/score")->assertRedirect();

    expect($lead->fresh()->score)->toBe(87)
        ->and($lead->fresh()->custom_fields['ai_reasons'])->toContain('Has a work email');
});

it('turns a meeting transcript into a linked activity and tasks', function () {
    [, $owner] = aiWorkspace();
    $contact = Contact::factory()->create(['owner_id' => $owner->id]);

    $this->mock(GeminiService::class, fn ($m) => $m->shouldReceive('summarizeMeeting')->once()->andReturn([
        'summary' => 'Discussed pricing and onboarding timeline.',
        'action_items' => ['Send the enterprise proposal', 'Schedule a technical demo'],
        'crm_updates' => [],
    ]));

    $this->actingAs($owner)->post("/contacts/{$contact->id}/meeting", ['transcript' => 'A: ... B: ...'])
        ->assertRedirect("/contacts/{$contact->id}");

    // Summary lands on the timeline as a meeting activity...
    expect($contact->activities()->where('type', 'meeting')->where('body', 'like', 'Discussed pricing%')->exists())->toBeTrue()
        // ...and each action item becomes a linked task.
        ->and(Task::where('subject_type', Contact::class)->where('subject_id', $contact->id)->count())->toBe(2);
});

it('degrades gracefully when AI is disabled for the workspace', function () {
    [$tenant] = aiWorkspace();
    $tenant->update(['ai_enabled' => false]);
    $lead = Lead::factory()->create();

    // Real service, no HTTP: falls back to a transparent signal-based score
    // (never throws, never a flat default).
    $result = app(GeminiService::class)->scoreLead($lead);

    expect($result['score'])->toBeGreaterThan(0)
        ->and($result['score'])->toBeLessThanOrEqual(100)
        ->and($result['reasons'])->toBeArray()->not->toBeEmpty();
});
