<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['name', 'trigger_event', 'trigger_config', 'enabled'];

    /** @var array<string, string> */
    protected $casts = ['trigger_config' => 'array', 'enabled' => 'boolean'];

    /** @return HasMany<WorkflowStep, $this> */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('order');
    }

    /** @return HasMany<WorkflowRun, $this> */
    public function runs(): HasMany
    {
        return $this->hasMany(WorkflowRun::class);
    }
}
