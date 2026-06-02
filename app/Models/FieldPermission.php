<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-tenant, per-role field-level permission. See FieldPermissionService for
 * how these are resolved against role defaults.
 *
 * @property string $tenant_id
 * @property string $role
 * @property string $entity
 * @property string $field_key
 * @property bool $can_view
 * @property bool $can_edit
 */
class FieldPermission extends Model implements TenantOwned
{
    use BelongsToTenant;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'role',
        'entity',
        'field_key',
        'can_view',
        'can_edit',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'can_view' => 'boolean',
        'can_edit' => 'boolean',
    ];
}
