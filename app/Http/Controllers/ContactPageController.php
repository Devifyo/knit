<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public marketing site: lead-generation / consultation page plus the Privacy
 * Policy and Terms pages required for SMS (toll-free / A2P) verification. Each
 * submission stores an auditable SMS opt-in record (exact text, IP, timestamp).
 */
class ContactPageController extends Controller
{
    /** The exact SMS opt-in consent shown to and agreed by the user. */
    public const SMS_CONSENT = 'I agree to receive SMS communications from Knit regarding my inquiry, project updates, service information, and promotional offers. Message frequency varies. Message and data rates may apply. Reply STOP to opt out at any time and HELP for assistance. Consent is not a condition of purchase.';

    public function contact(): Response
    {
        return Inertia::render('Public/Contact', $this->shared());
    }

    public function privacy(): Response
    {
        return Inertia::render('Public/Privacy', $this->shared());
    }

    public function terms(): Response
    {
        return Inertia::render('Public/Terms', $this->shared());
    }

    public function submit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'requirements' => ['nullable', 'string', 'max:5000'],
            // Must be ticked — the box is unchecked by default on the page.
            'sms_consent' => ['accepted'],
        ], [
            'sms_consent.accepted' => 'Please agree to receive SMS communications to submit the form.',
        ]);

        ContactSubmission::create([
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'requirements' => $data['requirements'] ?? null,
            'sms_consent' => true,
            'consent_text' => self::SMS_CONSENT,
            'consent_ip' => $request->ip(),
            'consent_user_agent' => substr((string) $request->userAgent(), 0, 1023),
            'consented_at' => now(),
        ]);

        return back()->with('success', "Thanks {$data['name']} — your request is in. Our team will reach out shortly.");
    }

    /**
     * Company details + consent text shared with every public page (single
     * source of truth so the displayed and stored consent always match).
     *
     * @return array<string, mixed>
     */
    private function shared(): array
    {
        return [
            'consentText' => self::SMS_CONSENT,
            'company' => [
                'name' => 'Knit',
                'legalName' => 'Knit by Devifyo',
                'email' => 'thagyal11@gmail.com',
                'website' => 'https://knit.devifyo.cloud',
                'hours' => 'Monday – Friday, 9:00 AM – 6:00 PM (IST)',
            ],
        ];
    }
}
