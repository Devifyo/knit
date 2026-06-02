<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Global ⌘K record search. Returns a flat, ranked list of matching CRM records
 * (contacts, companies, leads, deals) with a deep-link to each. Tenant isolation
 * is automatic via the global TenantScope on every model queried here.
 */
class SearchController extends Controller
{
    /** Max matches returned per entity type. */
    private const PER_TYPE = 5;

    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->string('q'));

        if ($q === '') {
            return response()->json(['results' => []]);
        }

        $user = $request->user();
        $like = '%'.$q.'%';
        $results = [];

        if ($user?->can('contacts.view')) {
            foreach (Contact::query()
                ->where(fn ($w) => $w
                    ->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like))
                ->limit(self::PER_TYPE)->get() as $c) {
                $results[] = ['id' => "contact-{$c->id}", 'label' => $c->name, 'sublabel' => $c->email, 'group' => 'Contact', 'href' => "/contacts/{$c->id}"];
            }
        }

        if ($user?->can('companies.view')) {
            foreach (Company::query()->where('name', 'like', $like)->limit(self::PER_TYPE)->get() as $co) {
                $results[] = ['id' => "company-{$co->id}", 'label' => $co->name, 'sublabel' => $co->domain, 'group' => 'Company', 'href' => '/companies'];
            }
        }

        if ($user?->can('deals.view')) {
            foreach (Deal::query()->where('name', 'like', $like)->limit(self::PER_TYPE)->get() as $d) {
                $results[] = ['id' => "deal-{$d->id}", 'label' => $d->name, 'sublabel' => $d->formattedAmount(), 'group' => 'Deal', 'href' => "/deals/{$d->id}"];
            }
        }

        if ($user?->can('leads.view')) {
            foreach (Lead::query()
                ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like))
                ->limit(self::PER_TYPE)->get() as $l) {
                $results[] = ['id' => "lead-{$l->id}", 'label' => $l->name, 'sublabel' => $l->email, 'group' => 'Lead', 'href' => '/leads'];
            }
        }

        return response()->json(['results' => $results]);
    }
}
