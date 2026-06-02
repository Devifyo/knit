<?php

declare(strict_types=1);

namespace App\Modules\Automation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(): Response
    {
        $tasks = Task::with('assignee:id,name')
            ->orderByRaw('completed_at is not null')
            ->orderBy('due_at')
            ->limit(200)
            ->get()
            ->map(fn (Task $t) => [
                'id' => $t->id,
                'title' => $t->title,
                'due_at' => $t->due_at?->toDayDateTimeString(),
                'assignee' => $t->assignee?->name,
                'done' => $t->isDone(),
            ])->all();

        return Inertia::render('Tasks/Index', ['tasks' => $tasks]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'due_at' => ['nullable', 'date'],
        ]);

        Task::create([
            'title' => $data['title'],
            'due_at' => $data['due_at'] ?? now()->addDay(),
            'assigned_user_id' => $request->user()->id,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Task created.');
    }

    public function toggle(Request $request, Task $task): RedirectResponse
    {
        $task->update(['completed_at' => $task->isDone() ? null : now()]);

        return back()->with('success', $task->isDone() ? 'Task completed.' : 'Task reopened.');
    }
}
