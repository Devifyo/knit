<?php

declare(strict_types=1);

namespace App\Modules\Support\Services;

use App\Models\Ticket;
use App\Models\User;

/**
 * Least-loaded routing: assign new tickets to the workspace member with the
 * fewest open tickets (round-robin in practice as load equalizes).
 */
class AssignmentService
{
    public function pickAssignee(): ?User
    {
        $members = User::where('tenant_id', tenant('id'))->get(['id', 'name']);
        if ($members->isEmpty()) {
            return null;
        }

        $load = Ticket::whereIn('status', ['open', 'pending'])
            ->selectRaw('assigned_user_id, count(*) as c')
            ->groupBy('assigned_user_id')
            ->pluck('c', 'assigned_user_id');

        return $members->sortBy(fn (User $u) => (int) ($load[$u->id] ?? 0))->first();
    }
}
