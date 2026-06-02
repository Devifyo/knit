<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Services;

use App\Models\Deal;
use App\Models\Lead;
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
        return ($filters['entity'] ?? 'deals') === 'leads'
            ? $this->leads($filters)
            : $this->deals($filters);
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
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<TModel>
     */
    protected function filter(Builder $query, array $filters, string $ownerColumn): Builder
    {
        if (! empty($filters['owner_id'])) {
            $query->where($ownerColumn, $filters['owner_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
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
