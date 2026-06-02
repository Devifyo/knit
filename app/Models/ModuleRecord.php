<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A record belonging to an installed industry module (e.g. a Real Estate
 * property). Shape is defined by the module manifest in ModuleRegistry; values
 * live in `data`. Tenant-scoped and optionally linked to a core CRM Contact.
 */
class ModuleRecord extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['module_key', 'entity_key', 'title', 'status', 'owner_id', 'contact_id', 'data'];

    /** @var array<string, string> */
    protected $casts = ['data' => 'array'];

    /** @return BelongsTo<Contact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
