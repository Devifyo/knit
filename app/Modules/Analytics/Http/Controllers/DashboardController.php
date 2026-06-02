<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $openValue = (int) Deal::where('status', 'open')->sum('amount');
        $wonValue = (int) Deal::where('status', 'won')->whereMonth('updated_at', now()->month)->sum('amount');

        $pipeline = Pipeline::with('stages')->where('type', 'deal')->orderByDesc('is_default')->first();
        $byStage = [];
        if ($pipeline) {
            foreach ($pipeline->stages as $stage) {
                $byStage[] = [
                    'name' => $stage->name,
                    'count' => Deal::where('stage_id', $stage->id)->count(),
                    'probability' => $stage->probability,
                ];
            }
        }

        // Win/loss ratio.
        $won = Deal::where('status', 'won')->count();
        $lost = Deal::where('status', 'lost')->count();

        // Team leaderboard (won revenue by owner) — shown to managers/owners.
        $isManager = $request->user()->hasAnyRole(['Owner', 'Admin', 'Manager']);
        $leaderboard = $isManager
            ? User::where('tenant_id', tenant('id'))->get(['id', 'name'])->map(fn (User $u) => [
                'name' => $u->name,
                'won' => (int) Deal::where('owner_id', $u->id)->where('status', 'won')->sum('amount'),
            ])->sortByDesc('won')->take(5)->values()->map(fn ($r) => [
                'name' => $r['name'], 'won' => number_format($r['won'] / 100).' USD',
            ])->all()
            : [];

        return Inertia::render('Dashboard', [
            'kpis' => [
                'open_deals' => Deal::where('status', 'open')->count(),
                'pipeline_value' => number_format($openValue / 100).' USD',
                'new_leads' => Lead::where('created_at', '>=', now()->subDays(7))->count(),
                'contacts' => Contact::count(),
            ],
            'won' => [
                'count' => Deal::where('status', 'won')->whereMonth('updated_at', now()->month)->count(),
                'revenue' => number_format($wonValue / 100).' USD',
                'win_rate' => ($won + $lost) > 0 ? round($won / ($won + $lost) * 100) : 0,
                'open_tickets' => Ticket::whereIn('status', ['open', 'pending'])->count(),
            ],
            'byStage' => $byStage,
            'leaderboard' => $leaderboard,
        ]);
    }
}
