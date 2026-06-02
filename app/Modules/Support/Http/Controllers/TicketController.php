<?php

declare(strict_types=1);

namespace App\Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Modules\Support\Channels\EmailChannelAdapter;
use App\Modules\Support\Services\TicketIntakeService;
use App\Services\AI\GeminiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = (string) $request->string('filter', 'open');

        $tickets = Ticket::with(['contact:id,first_name,last_name', 'assignee:id,name'])
            ->when($filter === 'open', fn ($q) => $q->whereIn('status', ['open', 'pending']))
            ->when($filter === 'breached', fn ($q) => $q->whereNotNull('escalated_at'))
            ->orderByRaw("field(priority,'urgent','high','normal','low')")
            ->orderBy('sla_due_at')
            ->limit(100)
            ->get()
            ->map(fn (Ticket $t): array => [
                'id' => $t->id,
                'number' => $t->number,
                'subject' => $t->subject,
                'contact' => $t->contact?->name,
                'assignee' => $t->assignee?->name,
                'status' => $t->status,
                'priority' => $t->priority,
                'channel' => $t->channel,
                'breached' => $t->escalated_at !== null,
                'due' => $t->first_responded_at ? 'responded' : ($t->sla_due_at?->diffForHumans() ?? '—'),
                'overdue' => $t->isBreached(),
            ])->all();

        return Inertia::render('Tickets/Index', ['tickets' => $tickets, 'filter' => $filter]);
    }

    public function show(Ticket $ticket): Response
    {
        $ticket->load(['contact:id,first_name,last_name', 'assignee:id,name', 'replies.user:id,name']);

        return Inertia::render('Tickets/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'number' => $ticket->number,
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'channel' => $ticket->channel,
                'contact' => $ticket->contact?->name,
                'contact_id' => $ticket->contact_id,
                'assignee' => $ticket->assignee?->name,
                'sla_due' => $ticket->sla_due_at?->diffForHumans(),
                'first_responded' => $ticket->first_responded_at?->diffForHumans(),
                'escalated' => $ticket->escalated_at !== null,
                'replies' => $ticket->replies->map(fn ($r) => [
                    'id' => $r->id,
                    'author' => optional($r->user)->name ?? optional($ticket->contact)->name ?? 'Customer',
                    'is_internal' => $r->is_internal,
                    'is_agent' => $r->user_id !== null,
                    'body' => $r->body,
                    'at' => $r->created_at?->diffForHumans(),
                ]),
            ],
            'members' => User::where('tenant_id', tenant('id'))->get(['id', 'name']),
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
            'internal' => ['boolean'],
        ]);

        $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'is_internal' => $data['internal'] ?? false,
            'body' => $data['body'],
        ]);

        // First public agent reply stops the SLA clock.
        if (! ($data['internal'] ?? false) && $ticket->first_responded_at === null) {
            $ticket->forceFill(['first_responded_at' => now(), 'status' => 'pending'])->save();
        }

        return back()->with('success', ($data['internal'] ?? false) ? 'Internal note added.' : 'Reply sent.');
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:open,pending,resolved,closed'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ]);

        if (isset($data['status'])) {
            $ticket->status = $data['status'];
            if (in_array($data['status'], ['resolved', 'closed'], true)) {
                $ticket->resolved_at = now();
            }
        }
        if (isset($data['priority'])) {
            $ticket->priority = $data['priority'];
        }
        if (array_key_exists('assigned_user_id', $data)) {
            $ticket->assigned_user_id = $data['assigned_user_id'];
        }
        $ticket->save();

        return back()->with('success', 'Ticket updated.');
    }

    /** AI assist: summarize the ticket + suggest reply drafts (flashed to the view). */
    public function assist(Ticket $ticket, GeminiService $ai): RedirectResponse
    {
        return back()->with('ai', [
            'summary' => $ai->summarizeTicket($ticket),
            'replies' => $ai->suggestReply($ticket),
        ]);
    }

    /** Dev helper: simulate an inbound support email (creates + routes a ticket). */
    public function simulate(Request $request, TicketIntakeService $intake): RedirectResponse
    {
        $data = $request->validate([
            'from_email' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
        ]);

        $ticket = $intake->fromChannel(new EmailChannelAdapter, $data, $data['priority']);

        return redirect("/tickets/{$ticket->id}")->with('success', 'Ticket created from inbound email.');
    }
}
