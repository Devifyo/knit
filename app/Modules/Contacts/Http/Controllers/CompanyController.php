<?php

declare(strict_types=1);

namespace App\Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(Request $request): Response
    {
        $search = (string) $request->string('search');

        $companies = Company::withCount('contacts')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (Company $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'domain' => $c->domain,
                'industry' => $c->industry,
                'size' => $c->size,
                'health_score' => $c->health_score,
                'contacts_count' => $c->contacts_count,
            ])->all();

        return Inertia::render('Companies/Index', ['companies' => $companies, 'search' => $search]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
        ]);

        Company::create([...$data, 'owner_id' => $request->user()->id]);

        return back()->with('success', 'Company created.');
    }
}
