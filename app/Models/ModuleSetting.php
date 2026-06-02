<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['key', 'enabled'];

    /** @var array<string, string> */
    protected $casts = ['enabled' => 'boolean'];
}
