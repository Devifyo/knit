<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use App\Modules\Admin\Services\Rbac;
use App\Modules\Billing\Services\Entitlements;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

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
            'canInvite' => request()->user()->can('members.invite'),
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

        // Plan gating — block inviting beyond the subscription's seat limit.
        if (! app(Entitlements::class)->canAddSeat()) {
            $limit = app(Entitlements::class)->seatLimit();

            return back()->withErrors(['email' => "Your plan includes {$limit} seats. Upgrade in Billing to add more."]);
        }

        Invitation::updateOrCreate(
            ['email' => $data['email']],
            ['role' => $data['role'], 'token' => Str::random(48), 'invited_by' => $request->user()->id, 'expires_at' => now()->addDays(7), 'accepted_at' => null],
        );

        // Mail is log-driver in dev; the shareable link is shown in the UI.
        return back()->with('success', 'Invitation created — share the link.');
    }

    public function revokeInvite(Invitation $invitation): RedirectResponse
    {
        $invitation->delete();

        return back()->with('success', 'Invitation revoked.');
    }
}
