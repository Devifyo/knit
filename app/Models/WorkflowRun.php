<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowRun extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['workflow_id', 'subject_type', 'subject_id', 'status', 'current_step', 'context', 'finished_at'];

    /** @var array<string, string> */
    protected $casts = ['context' => 'array', 'current_step' => 'integer', 'finished_at' => 'datetime'];

    /** @return BelongsTo<Workflow, $this> */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<WorkflowRunStep, $this> */
    public function runSteps(): HasMany
    {
        return $this->hasMany(WorkflowRunStep::class);
    }
}
