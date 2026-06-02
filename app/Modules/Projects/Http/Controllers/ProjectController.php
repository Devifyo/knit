<?php

declare(strict_types=1);

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectController extends Controller
{
    private const COLUMNS = ['todo' => 'To do', 'doing' => 'In progress', 'done' => 'Done'];

    public function index(): Response
    {
        $projects = Project::with(['owner:id,name', 'company:id,name', 'contact:id,first_name,last_name'])->withCount([
            'allTasks as tasks',
            'allTasks as done' => fn ($q) => $q->where('status', 'done'),
        ])->latest()->get()->map(fn (Project $p): array => [
            'id' => $p->id,
            'name' => $p->name,
            'owner' => $p->owner?->name,
            'customer' => optional($p->company)->name ?? optional($p->contact)->name,
            'tasks' => $p->getAttribute('tasks'),
            'done' => $p->getAttribute('done'),
            'progress' => $p->getAttribute('tasks') > 0 ? round($p->getAttribute('done') / $p->getAttribute('tasks') * 100) : 0,
        ])->all();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            // Open + won deals are eligible to spin up a delivery project.
            'deals' => Deal::with('company:id,name')->whereIn('status', ['open', 'won'])->latest()->get()
                ->map(fn (Deal $d): array => ['id' => $d->id, 'name' => $d->name, 'company' => $d->company?->name]),
        ]);
    }

    public function show(Project $project): Response
    {
        $project->load([
            'tasks.subtasks', 'tasks.assignee:id,name', 'tasks.timeEntries', 'media',
            'deal:id,name,amount,currency', 'company:id,name', 'contact:id,first_name,last_name',
        ]);

        $columns = collect(self::COLUMNS)->map(fn ($title, $status) => [
            'id' => $status,
            'title' => $title,
            'cards' => $project->tasks->where('status', $status)->values()->map(fn (ProjectTask $t) => [
                'id' => $t->id,
                'title' => $t->title,
                'assignee' => $t->assignee?->name,
                'minutes' => $t->minutesLogged(),
                'subtasks' => $t->subtasks->map(fn (ProjectTask $s) => ['id' => $s->id, 'title' => $s->title, 'status' => $s->status]),
            ])->all(),
        ])->values()->all();

        $totalMinutes = (int) $project->allTasks()->withSum('timeEntries', 'minutes')->get()->sum('time_entries_sum_minutes');

        return Inertia::render('Projects/Show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'owner' => $project->owner?->name,
                'total_hours' => round($totalMinutes / 60, 1),
                // Linked CRM records — clickable chips back into the graph.
                'deal' => $project->deal ? ['id' => $project->deal->id, 'name' => $project->deal->name, 'amount' => $project->deal->formattedAmount()] : null,
                'company' => $project->company ? ['id' => $project->company->id, 'name' => $project->company->name] : null,
                'contact' => $project->contact ? ['id' => $project->contact->id, 'name' => $project->contact->name] : null,
                'files' => $project->getMedia('files')->map(fn (Media $m) => [
                    'id' => $m->id, 'name' => $m->file_name, 'size' => round($m->size / 1024).' KB',
                ]),
            ],
            'columns' => $columns,
            'members' => User::where('tenant_id', tenant('id'))->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'deal_id' => ['nullable', 'exists:deals,id'],
        ]);

        // Like the popular CRMs: a project hangs off a deal and inherits that
        // deal's customer (company + contact) so the whole graph stays linked.
        $attributes = ['name' => $data['name'], 'description' => $data['description'] ?? null, 'owner_id' => $request->user()->id];
        if (! empty($data['deal_id'])) {
            $deal = Deal::findOrFail($data['deal_id']);
            $attributes['deal_id'] = $deal->id;
            $attributes['company_id'] = $deal->company_id;
            $attributes['contact_id'] = $deal->contact_id;
        }

        $project = Project::create($attributes);

        if (isset($deal)) {
            $deal->activities()->create([
                'type' => 'note',
                'body' => "Project “{$project->name}” started for this deal.",
                'user_id' => $request->user()->id,
            ]);
        }

        return redirect("/projects/{$project->id}")->with('success', 'Project created.');
    }

    public function storeTask(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:project_tasks,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $project->allTasks()->create([
            'title' => $data['title'],
            'parent_id' => $data['parent_id'] ?? null,
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
            'status' => 'todo',
            'position' => $project->allTasks()->count(),
        ]);

        return back()->with('success', 'Task added.');
    }

    public function moveTask(Request $request, ProjectTask $task): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:todo,doing,done'],
            'position' => ['nullable', 'integer'],
        ]);
        $task->update(['status' => $data['status'], 'position' => $data['position'] ?? 0]);

        return back()->with('success', 'Task moved.');
    }

    public function logTime(Request $request, ProjectTask $task): RedirectResponse
    {
        $data = $request->validate([
            'minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $task->timeEntries()->create([
            'user_id' => $request->user()->id,
            'minutes' => $data['minutes'],
            'note' => $data['note'] ?? null,
            'logged_at' => now(),
        ]);

        return back()->with('success', "Logged {$data['minutes']} min.");
    }

    public function uploadFile(Request $request, Project $project): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'max:10240']]);
        $project->addMediaFromRequest('file')->toMediaCollection('files');

        return back()->with('success', 'File uploaded.');
    }

    public function downloadFile(Project $project, Media $media): BinaryFileResponse
    {
        abort_unless($media->model_id === $project->id && $media->model_type === Project::class, 404);

        return response()->download($media->getPath(), $media->file_name);
    }
}
