<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['project_task_id', 'user_id', 'minutes', 'note', 'logged_at'];

    /** @var array<string, string> */
    protected $casts = ['logged_at' => 'datetime'];
}
