<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['company_id', 'health_score', 'renewal_date', 'renewal_status'];

    /** @var array<string, string> */
    protected $casts = ['renewal_date' => 'date', 'health_score' => 'integer'];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
