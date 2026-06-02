<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;

class WorkflowRunStep extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['workflow_run_id', 'workflow_step_id', 'status', 'output', 'ran_at'];

    /** @var array<string, string> */
    protected $casts = ['output' => 'array', 'ran_at' => 'datetime'];
}
