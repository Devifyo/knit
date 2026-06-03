<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        /** @var Contact $contact */
        $contact = Auth::guard('contact')->user();

        return Inertia::render('Portal/Dashboard', [
            'contact' => [
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'company' => $contact->company?->name,
            ],
            'stats' => [
                'open_tickets' => $contact->tickets()->whereIn('status', ['open', 'pending'])->count(),
                'tickets' => $contact->tickets()->count(),
                'deals' => $contact->deals()->count(),
            ],
            'recentTickets' => $contact->tickets()->latest()->limit(5)->get()->map(fn (Ticket $t) => [
                'id' => $t->id,
                'number' => $t->number,
                'subject' => $t->subject,
                'status' => $t->status,
                'priority' => $t->priority,
                'updated_at' => $t->updated_at?->diffForHumans(),
            ]),
            'deals' => $contact->deals()->limit(10)->get()->map(fn (Deal $d) => [
                'id' => $d->id,
                'name' => $d->name,
                'amount' => $d->formattedAmount(),
                'status' => $d->status,
            ]),
        ]);
    }
}
