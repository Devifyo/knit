<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;

class CustomFieldDefinition extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['entity', 'key', 'label', 'type', 'options', 'required', 'order'];

    /** @var array<string, string> */
    protected $casts = ['options' => 'array', 'required' => 'boolean', 'order' => 'integer'];
}
