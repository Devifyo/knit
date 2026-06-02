<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $openValue = (int) Deal::where('status', 'open')->sum('amount');

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

        return Inertia::render('Dashboard', [
            'kpis' => [
                'open_deals' => Deal::where('status', 'open')->count(),
                'pipeline_value' => number_format($openValue / 100).' USD',
                'new_leads' => Lead::where('created_at', '>=', now()->subDays(7))->count(),
                'contacts' => Contact::count(),
            ],
            'byStage' => $byStage,
        ]);
    }
}
