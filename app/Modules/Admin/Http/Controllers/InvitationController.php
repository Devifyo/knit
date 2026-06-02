<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Admin\Services\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

/**
 * Public invitation acceptance: the invitee opens /invite/{token} (no auth),
 * sets their name + password, and is created as a member of the inviting
 * workspace with the assigned role, then logged in.
 */
class InvitationController extends Controller
{
    public function show(string $token): Response|RedirectResponse
    {
        $invitation = $this->resolve($token);
        if (! $invitation) {
            return redirect('/login')->with('error', 'This invitation is invalid or has expired.');
        }

        $tenant = Tenant::find($invitation->tenant_id);

        return Inertia::render('Invitations/Accept', [
            'token' => $token,
            'email' => $invitation->email,
            'role' => $invitation->role,
            'workspace' => $tenant?->name,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->resolve($token);
        if (! $invitation) {
            throw ValidationException::withMessages(['token' => 'This invitation is invalid or has expired.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $tenant = Tenant::findOrFail($invitation->tenant_id);

        $user = User::create([
            'tenant_id' => $tenant->getTenantKey(),
            'name' => $data['name'],
            'email' => $invitation->email,
            'password' => Hash::make($data['password']),
        ]);

        // Assign the invited role within the workspace's team context.
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());
        $role = in_array($invitation->role, array_keys(Rbac::roles()), true) ? $invitation->role : Rbac::AGENT;
        $user->assignRole($role);

        $invitation->forceFill(['accepted_at' => now()])->save();

        Auth::login($user);

        return redirect('/dashboard')->with('success', "Welcome to {$tenant->name}!");
    }

    protected function resolve(string $token): ?Invitation
    {
        // No tenant context on this public route — look up across tenants.
        $invitation = Invitation::withoutGlobalScopes()->where('token', $token)->first();

        return $invitation && $invitation->isPending() ? $invitation : null;
    }
}
