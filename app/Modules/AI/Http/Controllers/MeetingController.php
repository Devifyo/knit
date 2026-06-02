<?php

declare(strict_types=1);

namespace App\Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Task;
use App\Services\AI\GeminiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Turns a meeting transcript into CRM records: an AI summary becomes a meeting
 * Activity on the contact's timeline, and each extracted action item becomes a
 * linked Task. This is the "meeting → auto CRM updates" flow.
 */
class MeetingController extends Controller
{
    public function summarize(Request $request, Contact $contact, GeminiService $ai): RedirectResponse
    {
        $data = $request->validate(['transcript' => ['required', 'string', 'max:20000']]);

        $result = $ai->summarizeMeeting($data['transcript']);
        $summary = $result['summary'] !== '' ? $result['summary'] : 'Meeting logged (AI summary unavailable).';

        // Summary → meeting activity on the contact timeline.
        $contact->activities()->create([
            'type' => 'meeting',
            'body' => $summary,
            'user_id' => $request->user()->id,
        ]);

        // Action items → linked tasks.
        foreach ($result['action_items'] as $item) {
            Task::create([
                'title' => $item,
                'due_at' => now()->addDays(2),
                'assigned_user_id' => $request->user()->id,
                'created_by' => $request->user()->id,
                'subject_type' => Contact::class,
                'subject_id' => $contact->id,
            ]);
        }

        $count = count($result['action_items']);

        return redirect("/contacts/{$contact->id}")
            ->with('success', "Meeting summarized — {$count} task(s) created from action items.");
    }
}
