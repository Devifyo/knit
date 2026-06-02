<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

/** @return array{0: Tenant, 1: User} */
function projectWorkspace(string $name, string $email): array
{
    $tenant = app(WorkspaceProvisioner::class)->provision([
        'name' => $name, 'owner_name' => $name.' Owner', 'email' => $email, 'password' => 'password',
    ]);
    tenancy()->initialize($tenant);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

    return [$tenant, $tenant->getRelation('owner')];
}

it('creates a project owned by the current user and lists it with progress', function () {
    [, $owner] = projectWorkspace('Acme', 'o@acme.test');

    $this->actingAs($owner)->post('/projects', ['name' => 'Onboarding revamp'])
        ->assertRedirect();

    $project = Project::first();
    expect($project->name)->toBe('Onboarding revamp')
        ->and($project->owner_id)->toBe($owner->id);

    $this->actingAs($owner)->get('/projects')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Projects/Index', false)->has('projects', 1));
});

it('adds a task and moves it across the kanban, persisting the status', function () {
    [, $owner] = projectWorkspace('Acme', 'o@acme.test');
    $project = Project::create(['name' => 'Board', 'owner_id' => $owner->id]);

    $this->actingAs($owner)->post("/projects/{$project->id}/tasks", ['title' => 'First task'])
        ->assertRedirect();

    $task = ProjectTask::first();
    expect($task->status)->toBe('todo')->and($task->position)->toBe(0);

    $this->actingAs($owner)->patch("/project-tasks/{$task->id}/move", ['status' => 'doing', 'position' => 2])
        ->assertRedirect();

    expect($task->fresh()->status)->toBe('doing')
        ->and($task->fresh()->position)->toBe(2);
});

it('nests a subtask under a parent task', function () {
    [, $owner] = projectWorkspace('Acme', 'o@acme.test');
    $project = Project::create(['name' => 'Board', 'owner_id' => $owner->id]);
    $parent = $project->allTasks()->create(['title' => 'Parent', 'status' => 'todo', 'position' => 0]);

    $this->actingAs($owner)->post("/projects/{$project->id}/tasks", [
        'title' => 'Child', 'parent_id' => $parent->id,
    ])->assertRedirect();

    expect($parent->subtasks()->count())->toBe(1)
        ->and($parent->subtasks()->first()->title)->toBe('Child');
});

it('logs time which rolls up to the task and project totals', function () {
    [, $owner] = projectWorkspace('Acme', 'o@acme.test');
    $project = Project::create(['name' => 'Board', 'owner_id' => $owner->id]);
    $task = $project->allTasks()->create(['title' => 'Work', 'status' => 'doing', 'position' => 0]);

    $this->actingAs($owner)->post("/project-tasks/{$task->id}/time", ['minutes' => 90, 'note' => 'Deep work'])
        ->assertRedirect();

    expect($task->fresh()->minutesLogged())->toBe(90);

    $this->actingAs($owner)->get("/projects/{$project->id}")
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Projects/Show', false)->where('project.total_hours', 1.5));
});

it('attaches a file and streams it back on download', function () {
    Storage::fake('public');
    [, $owner] = projectWorkspace('Acme', 'o@acme.test');
    $project = Project::create(['name' => 'Board', 'owner_id' => $owner->id]);

    $this->actingAs($owner)->post("/projects/{$project->id}/files", [
        'file' => UploadedFile::fake()->create('brief.pdf', 12, 'application/pdf'),
    ])->assertRedirect();

    $media = $project->fresh()->getFirstMedia('files');
    expect($media)->not->toBeNull()->and($media->file_name)->toBe('brief.pdf');

    $this->actingAs($owner)->get("/projects/{$project->id}/files/{$media->id}")
        ->assertOk()
        ->assertDownload('brief.pdf');
});

it('shows a workspace activity feed of recent events', function () {
    [, $owner] = projectWorkspace('Acme', 'o@acme.test');
    $contact = Contact::factory()->create(['owner_id' => $owner->id]);
    Activity::create([
        'type' => 'note', 'body' => 'Called the customer about renewal', 'user_id' => $owner->id,
        'subject_type' => Contact::class, 'subject_id' => $contact->id,
    ]);

    $this->actingAs($owner)->get('/feed')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Feed/Index', false)
            ->has('activities', 1)
            ->where('activities.0.body', 'Called the customer about renewal')
            ->where('activities.0.subject', 'Contact'));
});

it('links a project to a deal and inherits the deal’s company and contact', function () {
    [, $owner] = projectWorkspace('Acme', 'o@acme.test');
    $company = Company::factory()->create();
    $contact = Contact::factory()->create(['company_id' => $company->id, 'owner_id' => $owner->id]);
    $pipeline = Pipeline::where('is_default', true)->first();
    $deal = Deal::factory()->create([
        'pipeline_id' => $pipeline->id, 'stage_id' => $pipeline->stages()->first()->id,
        'company_id' => $company->id, 'contact_id' => $contact->id, 'owner_id' => $owner->id,
    ]);

    $this->actingAs($owner)->post('/projects', ['name' => 'Onboarding', 'deal_id' => $deal->id])
        ->assertRedirect();

    $project = Project::first();
    expect($project->deal_id)->toBe($deal->id)
        ->and($project->company_id)->toBe($company->id)
        ->and($project->contact_id)->toBe($contact->id)
        // ...the deal now lists the project, and an activity lands on its timeline.
        ->and($deal->projects()->count())->toBe(1)
        ->and($deal->activities()->where('body', 'like', 'Project%')->exists())->toBeTrue();

    // The project's show page exposes clickable links back into the graph.
    $this->actingAs($owner)->get("/projects/{$project->id}")
        ->assertInertia(fn ($p) => $p->component('Projects/Show', false)
            ->where('project.deal.id', $deal->id)
            ->where('project.company.name', $company->name)
            ->where('project.contact.name', $contact->name));
});

it('isolates projects between tenants', function () {
    [, $acmeOwner] = projectWorkspace('Acme', 'o@acme.test');
    Project::create(['name' => 'Acme project', 'owner_id' => $acmeOwner->id]);
    tenancy()->end();

    [, $globexOwner] = projectWorkspace('Globex', 'o@globex.test');
    $globexProject = Project::create(['name' => 'Globex project', 'owner_id' => $globexOwner->id]);
    tenancy()->end();

    $this->actingAs($acmeOwner)->get('/projects')
        ->assertOk()
        ->assertInertia(fn ($p) => $p->component('Projects/Index', false)->has('projects', 1));

    $this->actingAs($acmeOwner)->get("/projects/{$globexProject->id}")->assertNotFound();
});
