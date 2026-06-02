<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Support\Security\IpMatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $tenant = tenant();
        $pending = $user->two_factor_secret !== null && $user->two_factor_confirmed_at === null;

        return Inertia::render('Settings/Security', [
            'policy' => [
                'require_2fa' => (bool) $tenant->require_2fa,
                'allowed_ips' => implode("\n", (array) ($tenant->allowed_ips ?? [])),
            ],
            'twoFactor' => [
                'enabled' => $user->two_factor_confirmed_at !== null,
                'pending' => $pending,
                'qr' => $pending ? $user->twoFactorQrCodeSvg() : null,
                'recovery_codes' => $user->two_factor_secret !== null ? $user->recoveryCodes() : [],
            ],
            'sessions' => LoginActivity::where('user_id', $user->id)->latest('logged_in_at')->limit(10)->get()
                ->map(fn (LoginActivity $l): array => [
                    'id' => $l->id,
                    'ip' => $l->ip_address,
                    'agent' => $l->user_agent,
                    'at' => $l->logged_in_at->diffForHumans(),
                ]),
            'current_ip' => $request->ip(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'require_2fa' => ['required', 'boolean'],
            'allowed_ips' => ['nullable', 'string'],
        ]);

        $ips = collect(preg_split('/[\s,]+/', (string) ($data['allowed_ips'] ?? '')))
            ->map(fn ($ip) => trim((string) $ip))
            ->filter()
            ->values();

        foreach ($ips as $ip) {
            if (! $this->validIpOrCidr($ip)) {
                return back()->withErrors(['allowed_ips' => "“{$ip}” is not a valid IP address or CIDR range."]);
            }
        }

        // Lockout guard: a non-empty allow-list must include the admin's own IP,
        // otherwise the next request (including this settings page) is blocked.
        $current = (string) $request->ip();
        if ($ips->isNotEmpty() && ! $ips->contains(fn ($rule) => IpMatcher::matches($current, (string) $rule))) {
            return back()->withErrors(['allowed_ips' => "The allow-list must include your current IP ({$current}) or you'll lock yourself out."]);
        }

        tenant()->update([
            'require_2fa' => $data['require_2fa'],
            'allowed_ips' => $ips->all(),
        ]);

        return back()->with('success', 'Security policy updated.');
    }

    private function validIpOrCidr(string $value): bool
    {
        if (! str_contains($value, '/')) {
            return filter_var($value, FILTER_VALIDATE_IP) !== false;
        }

        [$ip, $bits] = explode('/', $value, 2);

        return filter_var($ip, FILTER_VALIDATE_IP) !== false
            && is_numeric($bits) && (int) $bits >= 0 && (int) $bits <= 128;
    }
}
