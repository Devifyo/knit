<?php

declare(strict_types=1);

namespace App\Modules\Communication\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Modules\Communication\Events\ChatMessageSent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(): Response
    {
        $messages = ChatMessage::with('user:id,name')->latest()->limit(50)->get()->reverse()->values()
            ->map(fn (ChatMessage $m) => [
                'id' => $m->id,
                'body' => $m->body,
                'author' => $m->user?->name,
                'user_id' => $m->user_id,
                'at' => $m->created_at?->diffForHumans(),
            ])->all();

        return Inertia::render('Chat/Index', ['messages' => $messages]);
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $message = ChatMessage::create(['user_id' => $request->user()->id, 'body' => $data['body']]);
        event(new ChatMessageSent($message, $request->user()->name));

        return back();
    }
}
