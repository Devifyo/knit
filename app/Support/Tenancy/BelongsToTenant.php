<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Applied to every tenant-owned model. It:
 *   1. registers the global {@see TenantScope} so reads can never leak across
 *      tenants, and
 *   2. auto-fills `tenant_id` on create from the active tenant.
 *
 * Models may override {@see getTenantColumn()} if they use a non-default key.
 * Cross-tenant isolation is proven by the mandatory Phase 1 isolation tests.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            $column = $model->getTenantColumn();

            if (empty($model->{$column})) {
                $tenantId = TenantScope::currentTenantId();

                if ($tenantId !== null) {
                    $model->{$column} = $tenantId;
                }
            }
        });
    }

    public function getTenantColumn(): string
    {
        return 'tenant_id';
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        /** @var class-string<Tenant> $model */
        $model = config('tenancy.tenant_model', Tenant::class);

        return $this->belongsTo($model, $this->getTenantColumn());
    }
}
