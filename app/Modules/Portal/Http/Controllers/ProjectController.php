<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Read-only delivery view: a customer sees the progress of projects linked to
 * them. Scoped through the authenticated contact's relationship.
 */
class ProjectController extends Controller
{
    private const COLUMNS = ['todo' => 'To do', 'doing' => 'In progress', 'done' => 'Done'];

    public function index(): Response
    {
        $projects = $this->contact()->projects()
            ->withCount(['allTasks as total_tasks', 'allTasks as done_tasks' => fn ($q) => $q->where('status', 'done')])
            ->get();

        return Inertia::render('Portal/Projects/Index', [
            'projects' => $projects->map(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'total' => $p->getAttribute('total_tasks'),
                'done' => $p->getAttribute('done_tasks'),
                'progress' => $p->getAttribute('total_tasks') > 0
                    ? (int) round($p->getAttribute('done_tasks') / $p->getAttribute('total_tasks') * 100)
                    : 0,
            ]),
        ]);
    }

    public function show(int $project): Response
    {
        $model = $this->contact()->projects()
            ->with(['tasks' => fn ($q) => $q->with('subtasks')])
            ->withCount(['allTasks as total_tasks', 'allTasks as done_tasks' => fn ($q) => $q->where('status', 'done')])
            ->findOrFail($project);

        $columns = collect(self::COLUMNS)->map(fn (string $title, string $status) => [
            'id' => $status,
            'title' => $title,
            'tasks' => $model->tasks->where('status', $status)->values()->map(fn (ProjectTask $t) => [
                'id' => $t->id,
                'title' => $t->title,
                'subtasks' => $t->subtasks->map(fn (ProjectTask $s) => ['title' => $s->title, 'done' => $s->status === 'done']),
            ]),
        ])->values();

        return Inertia::render('Portal/Projects/Show', [
            'project' => [
                'id' => $model->id,
                'name' => $model->name,
                'description' => $model->description,
                'status' => $model->status,
                'progress' => $model->getAttribute('total_tasks') > 0
                    ? (int) round($model->getAttribute('done_tasks') / $model->getAttribute('total_tasks') * 100)
                    : 0,
            ],
            'columns' => $columns,
        ]);
    }

    private function contact(): Contact
    {
        /** @var Contact $contact */
        $contact = Auth::guard('contact')->user();

        return $contact;
    }
}
