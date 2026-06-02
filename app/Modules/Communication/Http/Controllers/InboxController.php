<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Modules\Communication\Events\UserMentioned;
use App\Modules\Communication\Services\InboundEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class InboxController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = (string) $request->string('filter', 'open');

        $conversations = Conversation::with(['contact:id,first_name,last_name', 'assignee:id,name'])
            ->withCount(['messages as unread' => fn ($q) => $q->where('direction', 'inbound')->whereNull('read_at')])
            ->when($filter === 'open', fn ($q) => $q->where('status', 'open'))
            ->when($filter === 'mine', fn ($q) => $q->where('assigned_user_id', $request->user()->id))
            ->orderByDesc('last_message_at')
            ->limit(100)
            ->get()
            ->map(fn (Conversation $c): array => [
                'id' => $c->id,
                'subject' => $c->subject,
                'contact' => $c->contact?->name,
                'assignee' => $c->assignee?->name,
                'status' => $c->status,
                'unread' => (int) $c->getAttribute('unread'),
                'last_at' => $c->last_message_at?->diffForHumans(),
            ])->all();

        return Inertia::render('Inbox/Index', ['conversations' => $conversations, 'filter' => $filter]);
    }

    public function show(Request $request, Conversation $conversation): Response
    {
        $conversation->load(['contact:id,first_name,last_name', 'assignee:id,name', 'messages.user:id,name']);
        // Mark inbound messages read.
        $conversation->messages()->where('direction', 'inbound')->whereNull('read_at')->update(['read_at' => now()]);

        return Inertia::render('Inbox/Show', [
            'conversation' => [
                'id' => $conversation->id,
                'subject' => $conversation->subject,
                'status' => $conversation->status,
                'contact' => $conversation->contact?->name,
                'contact_id' => $conversation->contact_id,
                'assignee' => $conversation->assignee?->name,
                'messages' => $conversation->messages->map(fn (Message $m) => [
                    'id' => $m->id,
                    'direction' => $m->direction,
                    'is_internal' => $m->is_internal,
                    'from' => $m->is_internal ? ($m->user?->name.' (note)') : ($m->from_name ?: $m->from_email ?: $m->user?->name),
                    'body' => $m->body,
                    'at' => $m->created_at?->diffForHumans(),
                ]),
            ],
            'members' => User::where('tenant_id', tenant('id'))->get(['id', 'name']),
        ]);
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:10000']]);

        $to = $conversation->messages()->where('direction', 'inbound')->latest()->value('from_email');
        $conversation->messages()->create([
            'direction' => 'outbound',
            'from_email' => $request->user()->email,
            'to_email' => $to,
            'body' => $data['body'],
            'user_id' => $request->user()->id,
            'read_at' => now(),
        ]);
        $conversation->update(['last_message_at' => now()]);

        if ($to) {
            Mail::raw($data['body'], fn ($m) => $m->to($to)->subject('Re: '.$conversation->subject));
        }
        if ($conversation->contact_id) {
            Activity::create([
                'type' => 'email', 'subject_type' => Contact::class, 'subject_id' => $conversation->contact_id,
                'body' => 'Outbound email: '.Str::limit($conversation->subject, 80), 'user_id' => $request->user()->id,
            ]);
        }

        return back()->with('success', 'Reply sent.');
    }

    public function note(Request $request, Conversation $conversation): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:10000']]);

        $conversation->messages()->create([
            'direction' => 'outbound', 'is_internal' => true,
            'body' => $data['body'], 'user_id' => $request->user()->id, 'read_at' => now(),
        ]);

        // @mentions → notify mentioned teammates live.
        foreach (User::where('tenant_id', tenant('id'))->get() as $member) {
            if ($member->id !== $request->user()->id && Str::contains($data['body'], '@'.$member->name)) {
                event(new UserMentioned($member->id, (string) tenant('id'),
                    "{$request->user()->name} mentioned you in “{$conversation->subject}”"));
            }
        }

        return back()->with('success', 'Note added.');
    }

    public function assign(Request $request, Conversation $conversation): RedirectResponse
    {
        $data = $request->validate(['assigned_user_id' => ['nullable', 'exists:users,id']]);
        $conversation->update(['assigned_user_id' => $data['assigned_user_id'] ?? null]);

        return back()->with('success', 'Assignment updated.');
    }

    public function status(Request $request, Conversation $conversation): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:open,closed,snoozed']]);
        $conversation->update(['status' => $data['status']]);

        return back()->with('success', "Conversation {$data['status']}.");
    }

    /** Demo helper: simulate an inbound email (no real mail provider in dev). */
    public function simulate(Request $request, InboundEmailService $service): RedirectResponse
    {
        $data = $request->validate([
            'from_email' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'in_reply_to' => ['nullable', 'string'],
        ]);

        $message = $service->handle([
            'from_email' => $data['from_email'],
            'from_name' => Str::before($data['from_email'], '@'),
            'subject' => $data['subject'],
            'body' => $data['body'],
            'message_id' => (string) Str::uuid(),
            'in_reply_to' => $data['in_reply_to'] ?? null,
        ]);

        return redirect("/inbox/{$message->conversation_id}")->with('success', 'Inbound email received.');
    }
}
