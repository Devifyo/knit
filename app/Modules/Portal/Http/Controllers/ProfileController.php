<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        /** @var Contact $contact */
        $contact = Auth::guard('contact')->user();

        return Inertia::render('Portal/Profile', [
            'contact' => $contact->only('first_name', 'last_name', 'email', 'phone', 'job_title'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var Contact $contact */
        $contact = Auth::guard('contact')->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'job_title' => ['nullable', 'string', 'max:120'],
        ]);

        $contact->update($data);

        return back()->with('success', 'Profile updated.');
    }
}
