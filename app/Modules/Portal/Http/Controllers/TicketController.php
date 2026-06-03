<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Ticket;
use App\Modules\Support\Events\TicketCreated;
use App\Modules\Support\Services\AssignmentService;
use App\Modules\Support\Services\SlaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The customer's own support tickets. Every query is scoped through the
 * authenticated contact's relationship, so a contact can only ever reach their
 * own tickets within their workspace.
 */
class TicketController extends Controller
{
    public function __construct(
        protected SlaService $sla,
        protected AssignmentService $assignment,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Portal/Tickets/Index', [
            'tickets' => $this->contact()->tickets()->get()->map(fn (Ticket $t) => [
                'id' => $t->id,
                'number' => $t->number,
                'subject' => $t->subject,
                'status' => $t->status,
                'priority' => $t->priority,
                'updated_at' => $t->updated_at?->diffForHumans(),
            ]),
        ]);
    }

    public function show(int $ticket): Response
    {
        $model = $this->contact()->tickets()->with(['replies' => fn ($q) => $q->where('is_internal', false)->with('user:id,name')])->findOrFail($ticket);

        return Inertia::render('Portal/Tickets/Show', [
            'ticket' => [
                'id' => $model->id,
                'number' => $model->number,
                'subject' => $model->subject,
                'status' => $model->status,
                'priority' => $model->priority,
                'created_at' => $model->created_at?->toDayDateTimeString(),
                // The customer sees only public replies — never internal notes.
                'replies' => $model->replies->map(fn ($r) => [
                    'id' => $r->id,
                    'body' => $r->body,
                    'from' => $r->user_id ? (optional($r->user)->name ?? 'Support') : 'You',
                    'is_agent' => $r->user_id !== null,
                    'at' => $r->created_at?->diffForHumans(),
                ]),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
        ]);

        $contact = $this->contact();
        $ticket = Ticket::create([
            'number' => 'T-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'subject' => $data['subject'],
            'body' => $data['body'],
            'contact_id' => $contact->id,
            'channel' => 'portal',
            'status' => 'open',
            'priority' => $data['priority'],
            'sla_due_at' => $this->sla->dueAt($data['priority']),
            'assigned_user_id' => $this->assignment->pickAssignee()?->id,
        ]);
        $ticket->replies()->create(['user_id' => null, 'body' => $data['body']]);

        event(new TicketCreated($ticket));

        return redirect("/portal/tickets/{$ticket->id}")->with('success', 'Ticket submitted.');
    }

    public function reply(Request $request, int $ticket): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:10000']]);

        $model = $this->contact()->tickets()->findOrFail($ticket);
        $model->replies()->create(['user_id' => null, 'body' => $data['body']]);

        // A customer reply re-opens a resolved/closed ticket.
        if (in_array($model->status, ['resolved', 'closed'], true)) {
            $model->forceFill(['status' => 'open'])->save();
        }

        return back()->with('success', 'Reply sent.');
    }

    private function contact(): Contact
    {
        /** @var Contact $contact */
        $contact = Auth::guard('contact')->user();

        return $contact;
    }
}
