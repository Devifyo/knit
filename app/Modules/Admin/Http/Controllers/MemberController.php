<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(): Response
    {
        // Users carry no global TenantScope (auth needs them unscoped), so we
        // scope members explicitly to the current workspace.
        $members = User::query()
            ->where('tenant_id', tenant('id'))
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'roles' => $u->getRoleNames(),
            ]);

        return Inertia::render('Members/Index', ['members' => $members]);
    }
}
