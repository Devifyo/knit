<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use OwenIt\Auditing\Models\Audit;

/**
 * Read-only audit trail (owen-it/laravel-auditing). Scoped to the current
 * workspace by filtering on its users — audits are recorded against globally
 * unique model IDs but we only surface those performed by this tenant's members.
 */
class AuditController extends Controller
{
    public function index(): Response
    {
        /** @var array<int, string> $userNames */
        $userNames = User::pluck('name', 'id')->all();

        $records = Audit::whereIn('user_id', array_keys($userNames))->latest()->limit(100)->get();

        $audits = [];
        foreach ($records as $a) {
            $audits[] = [
                'id' => $a->getKey(),
                'event' => $a->event,
                'model' => class_basename((string) $a->auditable_type),
                'model_id' => $a->auditable_id,
                'user' => $userNames[$a->getAttribute('user_id')] ?? 'System',
                'changes' => array_keys($a->getModified()),
                'ip' => $a->getAttribute('ip_address'),
                'at' => $a->created_at?->diffForHumans(),
            ];
        }

        return Inertia::render('Settings/Audit', ['audits' => $audits]);
    }
}
