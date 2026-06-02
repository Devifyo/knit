<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BrandingController extends Controller
{
    public function edit(): Response
    {
        $tenant = tenant();

        return Inertia::render('Settings/Branding', [
            'branding' => [
                'name' => $tenant->name,
                'brand_color' => $tenant->brand_color,
                'logo_url' => $tenant->logo_path ? "/storage/{$tenant->logo_path}" : null,
                'ai_enabled' => (bool) $tenant->ai_enabled,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand_color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'ai_enabled' => ['boolean'],
        ]);

        $tenant = tenant();
        $tenant->name = $data['name'];
        $tenant->brand_color = $data['brand_color'];
        $tenant->ai_enabled = $data['ai_enabled'] ?? false;

        if ($request->hasFile('logo')) {
            $tenant->logo_path = $request->file('logo')->store('branding', 'public');
        }

        $tenant->save();

        return back()->with('success', 'Settings updated.');
    }
}
