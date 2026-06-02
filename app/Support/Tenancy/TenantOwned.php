<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

/**
 * Marks an Eloquent model as tenant-owned. Implemented in practice by applying
 * the {@see BelongsToTenant} trait, which provides {@see getTenantColumn()} and
 * registers the global {@see TenantScope}.
 */
interface TenantOwned
{
    /**
     * The column holding the owning tenant's key.
     */
    public function getTenantColumn(): string;
}
