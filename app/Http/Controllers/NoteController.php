<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\NoteCreated;
use App\Http\Requests\StoreNoteRequest;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NoteController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Note::class);

        // TenantScope guarantees only the current workspace's notes are returned.
        $notes = Note::with('user:id,name')->latest()->get()
            ->map(fn (Note $n): array => [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'author' => $n->user?->name,
                'created_at' => $n->created_at?->toDayDateTimeString(),
            ])
            ->all();

        return Inertia::render('Notes/Index', ['notes' => $notes]);
    }

    public function store(StoreNoteRequest $request): RedirectResponse
    {
        // tenant_id is auto-filled by BelongsToTenant.
        $note = Note::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        event(new NoteCreated($note));

        return back()->with('success', 'Note created.');
    }

    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return back()->with('success', 'Note deleted.');
    }
}
