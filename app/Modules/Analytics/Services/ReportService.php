<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Services;

use App\Models\Campaign;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Ticket;
use App\Modules\Deals\Services\PricingService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Custom report builder. Applies owner + date-range + status filters to a chosen
 * entity and returns rows + headers + a summary, shared by the on-screen view and
 * every export format (CSV / Excel / PDF).
 */
class ReportService
{
    public function __construct(protected PricingService $pricing) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function build(array $filters): array
    {
        return match ($filters['entity'] ?? 'deals') {
            'leads' => $this->leads($filters),
            'contacts' => $this->contacts($filters),
            'companies' => $this->companies($filters),
            'tickets' => $this->tickets($filters),
            'campaigns' => $this->campaigns($filters),
            default => $this->deals($filters),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function deals(array $filters): array
    {
        $deals = $this->filter(Deal::query()->with(['owner:id,name', 'stage:id,name']), $filters, 'owner_id')
            ->latest()->limit(5000)->get();

        $rows = $deals->map(fn (Deal $d) => [
            $d->name,
            optional($d->owner)->name ?? '—',
            optional($d->stage)->name ?? '—',
            $this->pricing->format((int) $d->amount, $d->currency),
            $d->status,
            $d->created_at?->toDateString() ?? '',
        ])->all();

        return [
            'title' => 'Deals report',
            'headers' => ['Deal', 'Owner', 'Stage', 'Amount', 'Status', 'Created'],
            'rows' => $rows,
            'summary' => [
                'Deals' => (string) $deals->count(),
                'Total value' => $this->pricing->format((int) $deals->sum('amount'), 'USD'),
                'Won' => (string) $deals->where('status', 'won')->count(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function leads(array $filters): array
    {
        $leads = $this->filter(Lead::query()->with('assignee:id,name'), $filters, 'assigned_user_id')
            ->latest()->limit(5000)->get();

        $rows = $leads->map(fn (Lead $l) => [
            $l->name,
            optional($l->assignee)->name ?? '—',
            $l->status,
            (string) $l->score,
            $l->source ?? '—',
            $l->created_at?->toDateString() ?? '',
        ])->all();

        return [
            'title' => 'Leads report',
            'headers' => ['Lead', 'Owner', 'Status', 'Score', 'Source', 'Created'],
            'rows' => $rows,
            'summary' => [
                'Leads' => (string) $leads->count(),
                'Avg score' => (string) round((float) $leads->avg('score')),
                'Qualified' => (string) $leads->where('status', 'qualified')->count(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function contacts(array $filters): array
    {
        $contacts = $this->filter(Contact::query()->with(['owner:id,name', 'company:id,name']), $filters, 'owner_id', null)
            ->latest()->limit(5000)->get();

        $rows = $contacts->map(fn (Contact $c) => [
            $c->name,
            optional($c->owner)->name ?? '—',
            optional($c->company)->name ?? '—',
            $c->email ?? '—',
            $c->lifecycle_stage ?? '—',
            $c->created_at?->toDateString() ?? '',
        ])->all();

        return [
            'title' => 'Contacts report',
            'headers' => ['Contact', 'Owner', 'Company', 'Email', 'Stage', 'Created'],
            'rows' => $rows,
            'summary' => [
                'Contacts' => (string) $contacts->count(),
                'With company' => (string) $contacts->whereNotNull('company_id')->count(),
                'Customers' => (string) $contacts->where('lifecycle_stage', 'customer')->count(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function companies(array $filters): array
    {
        $companies = $this->filter(Company::query()->with('owner:id,name')->withCount('contacts'), $filters, 'owner_id', null)
            ->latest()->limit(5000)->get();

        $rows = $companies->map(fn (Company $c) => [
            $c->name,
            optional($c->owner)->name ?? '—',
            $c->industry ?? '—',
            (string) $c->health_score,
            (string) $c->contacts_count,
            $c->created_at?->toDateString() ?? '',
        ])->all();

        return [
            'title' => 'Companies report',
            'headers' => ['Company', 'Owner', 'Industry', 'Health', 'Contacts', 'Created'],
            'rows' => $rows,
            'summary' => [
                'Companies' => (string) $companies->count(),
                'Avg health' => (string) round((float) $companies->avg('health_score')),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function tickets(array $filters): array
    {
        $tickets = $this->filter(Ticket::query()->with('assignee:id,name'), $filters, 'assigned_user_id')
            ->latest()->limit(5000)->get();

        $rows = $tickets->map(fn (Ticket $t) => [
            $t->number ?? ('#'.$t->id),
            $t->subject,
            optional($t->assignee)->name ?? '—',
            $t->status,
            $t->priority,
            $t->created_at?->toDateString() ?? '',
        ])->all();

        return [
            'title' => 'Tickets report',
            'headers' => ['Ticket', 'Subject', 'Owner', 'Status', 'Priority', 'Created'],
            'rows' => $rows,
            'summary' => [
                'Tickets' => (string) $tickets->count(),
                'Open' => (string) $tickets->whereIn('status', ['open', 'pending'])->count(),
                'Resolved' => (string) $tickets->where('status', 'resolved')->count(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function campaigns(array $filters): array
    {
        $campaigns = $this->filter(Campaign::query()->withCount('recipients'), $filters, null)
            ->latest()->limit(5000)->get();

        $rows = $campaigns->map(fn (Campaign $c) => [
            $c->name,
            $c->status,
            $c->audience ?? '—',
            (string) $c->recipients_count,
            $c->created_at?->toDateString() ?? '',
        ])->all();

        return [
            'title' => 'Campaigns report',
            'headers' => ['Campaign', 'Status', 'Audience', 'Recipients', 'Created'],
            'rows' => $rows,
            'summary' => [
                'Campaigns' => (string) $campaigns->count(),
                'Sent' => (string) $campaigns->where('status', 'sent')->count(),
            ],
        ];
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<TModel>
     */
    protected function filter(Builder $query, array $filters, ?string $ownerColumn, ?string $statusColumn = 'status'): Builder
    {
        if ($ownerColumn && ! empty($filters['owner_id'])) {
            $query->where($ownerColumn, $filters['owner_id']);
        }
        if ($statusColumn && ! empty($filters['status'])) {
            $query->where($statusColumn, $filters['status']);
        }
        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query;
    }
}
