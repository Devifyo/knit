<?php

declare(strict_types=1);

namespace App\Modules\Accounts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(): Response
    {
        $accounts = Account::with('company:id,name,industry')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (Account $a) => [
                'id' => $a->id,
                'company' => $a->company?->name,
                'industry' => $a->company?->industry,
                'health_score' => $a->health_score,
                'renewal_date' => $a->renewal_date?->toFormattedDateString(),
                'renewal_status' => $a->renewal_status,
            ])->all();

        return Inertia::render('Accounts/Index', ['accounts' => $accounts]);
    }
}
