<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Workspace activity feed — a unified stream of everything happening across the
 * CRM (notes, calls, emails, meetings, system events) for the whole team.
 */
class ActivityFeedController extends Controller
{
    public function index(): Response
    {
        $activities = Activity::with('user:id,name')->latest()->limit(80)->get()
            ->map(fn (Activity $a): array => [
                'id' => $a->id,
                'type' => $a->type,
                'body' => $a->body,
                'author' => optional($a->user)->name ?? 'System',
                'subject' => class_basename((string) $a->subject_type),
                'subject_id' => $a->subject_id,
                'at' => $a->created_at?->diffForHumans(),
            ])->all();

        return Inertia::render('Feed/Index', ['activities' => $activities]);
    }
}
