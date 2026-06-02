<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that constrains every query on a tenant-owned model to the
 * currently-initialized tenant. Combined with {@see BelongsToTenant}, this is
 * the backbone of cross-tenant isolation (docs/ARCHITECTURE.md §Multi-tenancy).
 *
 * When no tenant is initialized (central/landlord context) the scope is a
 * no-op so landlord tooling can still query across the table intentionally.
 *
 * @implements Scope<Model>
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = static::currentTenantId();

        // Only tenant-owned models carry getTenantColumn(); guard for safety.
        if ($tenantId !== null && $model instanceof TenantOwned) {
            $builder->where(
                $model->getTable().'.'.$model->getTenantColumn(),
                $tenantId,
            );
        }
    }

    public static function currentTenantId(): int|string|null
    {
        $tenant = function_exists('tenant') ? tenant() : null;

        return $tenant?->getTenantKey();
    }
}
