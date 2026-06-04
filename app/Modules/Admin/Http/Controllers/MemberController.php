<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use App\Modules\Admin\Mail\MemberCredentialsMail;
use App\Modules\Admin\Services\Rbac;
use App\Modules\Billing\Services\Entitlements;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MemberController extends Controller
{
    public function index(): Response
    {
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

        $invitations = Invitation::whereNull('accepted_at')->latest()->get()
            ->map(fn (Invitation $i) => [
                'id' => $i->id,
                'email' => $i->email,
                'role' => $i->role,
                'expired' => ! $i->isPending(),
                'link' => url('/invite/'.$i->token),
            ]);

        return Inertia::render('Members/Index', [
            'members' => $members,
            'invitations' => $invitations,
            'roles' => array_keys(Rbac::roles()),
            'ownerRole' => Rbac::OWNER,
            'canInvite' => request()->user()->can('members.invite'),
            'canManageRoles' => request()->user()->can('roles.manage'),
            'permissionGroups' => Rbac::permissionGroups(),
            'rolePermissions' => $this->rolePermissions(),
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:'.implode(',', array_keys(Rbac::roles()))],
        ]);

        if (User::where('tenant_id', tenant('id'))->where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => 'That person is already a member.']);
        }
        if ($seatError = $this->seatGuard()) {
            return $seatError;
        }

        Invitation::updateOrCreate(
            ['email' => $data['email']],
            ['role' => $data['role'], 'token' => Str::random(48), 'invited_by' => $request->user()->id, 'expires_at' => now()->addDays(7), 'accepted_at' => null],
        );

        return back()->with('success', 'Invitation created — share the link.');
    }

    /** Create a member account directly and email them their credentials. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:'.implode(',', array_keys(Rbac::roles()))],
        ]);

        if (User::where('tenant_id', tenant('id'))->where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => 'That person is already a member.']);
        }
        if ($seatError = $this->seatGuard()) {
            return $seatError;
        }

        $password = Str::password(14);
        $user = User::create([
            'tenant_id' => tenant('id'),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $password, // hashed via the model cast
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));
        $user->assignRole($data['role']);

        $workspace = (string) (tenant('name') ?? 'your workspace');
        try {
            Mail::to($user->email)->send(new MemberCredentialsMail($user->name, $user->email, $password, $data['role'], $workspace));
            $msg = "{$user->name} added — credentials emailed.";
        } catch (\Throwable) {
            $msg = "{$user->name} added. (Email couldn't be sent — share their temporary password: {$password})";
        }

        return back()->with('success', $msg);
    }

    public function revokeInvite(Invitation $invitation): RedirectResponse
    {
        $invitation->delete();

        return back()->with('success', 'Invitation revoked.');
    }

    /** Update what a role can do (Owner is always full-access and not editable). */
    public function updateRolePermissions(Request $request, string $role): RedirectResponse
    {
        abort_unless($request->user()->can('roles.manage'), 403);
        abort_if($role === Rbac::OWNER, 403, 'The Owner role always has full access.');
        abort_unless(array_key_exists($role, Rbac::roles()), 404);

        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(Rbac::permissions())],
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));
        $roleModel = Role::where('name', $role)->where('tenant_id', tenant('id'))->firstOrFail();
        $perms = Permission::whereIn('name', $data['permissions'] ?? [])->get();
        $roleModel->syncPermissions($perms);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', "{$role} permissions updated.");
    }

    /**
     * Current permission keys per role for this tenant.
     *
     * @return array<string, array<int, string>>
     */
    private function rolePermissions(): array
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(tenant('id'));
        $out = [];
        foreach (array_keys(Rbac::roles()) as $name) {
            if ($name === Rbac::OWNER) {
                $out[$name] = Rbac::permissions(); // owner = everything

                continue;
            }
            $role = Role::where('name', $name)->where('tenant_id', tenant('id'))->first();
            $out[$name] = $role ? $role->permissions->pluck('name')->all() : [];
        }

        return $out;
    }

    private function seatGuard(): ?RedirectResponse
    {
        if (! app(Entitlements::class)->canAddSeat()) {
            $limit = app(Entitlements::class)->seatLimit();

            return back()->withErrors(['email' => "Your plan includes {$limit} seats. Upgrade in Billing to add more."]);
        }

        return null;
    }
}
