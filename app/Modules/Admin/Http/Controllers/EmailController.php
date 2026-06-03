<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Services\TenantMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Per-workspace SMTP settings. When configured, all of this workspace's outgoing
 * mail uses it; otherwise the platform's env SMTP is used as a fallback.
 */
class EmailController extends Controller
{
    public function edit(): Response
    {
        $tenant = tenant();

        return Inertia::render('Settings/Email', [
            'mail' => [
                'configured' => $tenant->hasCustomMail(),
                'host' => $tenant->smtp_host,
                'port' => $tenant->smtp_port,
                'username' => $tenant->smtp_username,
                'encryption' => $tenant->smtp_encryption,
                'from_address' => $tenant->smtp_from_address,
                'from_name' => $tenant->smtp_from_name,
                'has_password' => filled($tenant->smtp_password),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535', 'required_with:host'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', Rule::in(['tls', 'ssl'])],
            'from_address' => ['nullable', 'email', 'required_with:host'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $tenant = tenant();
        $host = $data['host'] ?: null;

        $tenant->smtp_host = $host;
        $tenant->smtp_port = $host ? ($data['port'] ?? null) : null;
        $tenant->smtp_username = $host ? ($data['username'] ?? null) : null;
        $tenant->smtp_encryption = $host ? ($data['encryption'] ?? null) : null;
        $tenant->smtp_from_address = $host ? ($data['from_address'] ?? null) : null;
        $tenant->smtp_from_name = $host ? ($data['from_name'] ?? null) : null;

        // Only overwrite the password when a new one is supplied; clear it when
        // custom mail is turned off.
        if (! $host) {
            $tenant->smtp_password = null;
        } elseif (filled($data['password'] ?? null)) {
            $tenant->smtp_password = $data['password'];
        }

        $tenant->save();
        app(TenantMail::class)->apply($tenant->fresh());

        return back()->with('success', $host ? 'Email settings saved.' : 'Reverted to the platform default email.');
    }

    public function test(Request $request): RedirectResponse
    {
        app(TenantMail::class)->apply(tenant());
        $to = $request->user('web')->email;

        try {
            Mail::raw("This is a test email from your {$request->user('web')->name} workspace on Knit. If you received it, your email is configured correctly.", function ($m) use ($to) {
                $m->to($to)->subject('Knit — email configuration test');
            });

            return back()->with('success', "Test email sent to {$to}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Test failed: '.$e->getMessage());
        }
    }
}
