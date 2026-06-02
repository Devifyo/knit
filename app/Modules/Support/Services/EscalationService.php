<?php

declare(strict_types=1);

namespace App\Modules\Support\Services;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\Ticket;
use App\Models\User;
use App\Modules\Admin\Services\Rbac;
use App\Modules\Support\Events\TicketEscalated;
use Spatie\Permission\PermissionRegistrar;

/**
 * Escalates tickets that breached their first-response SLA: bumps priority,
 * reassigns to a manager/owner, timelines it, and notifies. Idempotent — a
 * ticket is escalated at most once (escalated_at guard). Runs within a tenant.
 */
class EscalationService
{
    private const BUMP = ['low' => 'normal', 'normal' => 'high', 'high' => 'urgent', 'urgent' => 'urgent'];

    public function escalateBreached(): int
    {
        $breached = Ticket::whereIn('status', ['open', 'pending'])
            ->whereNull('first_responded_at')
            ->whereNull('escalated_at')
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->get();

        foreach ($breached as $ticket) {
            $this->escalate($ticket);
        }

        return $breached->count();
    }

    public function escalate(Ticket $ticket): void
    {
        $ticket->forceFill([
            'escalated_at' => now(),
            'priority' => self::BUMP[$ticket->priority] ?? 'high',
            'assigned_user_id' => optional($this->manager())->id ?? $ticket->assigned_user_id,
        ])->save();

        if ($ticket->contact_id) {
            Activity::create([
                'type' => 'system',
                'subject_type' => Contact::class,
                'subject_id' => $ticket->contact_id,
                'body' => "Ticket {$ticket->number} escalated (SLA breach).",
            ]);
        }

        event(new TicketEscalated($ticket));
    }

    protected function manager(): ?User
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));

        return User::where('tenant_id', tenant('id'))->get()
            ->first(fn (User $u) => $u->hasAnyRole([Rbac::MANAGER, Rbac::OWNER, Rbac::ADMIN]));
    }
}
