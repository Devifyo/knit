<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Form extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['name', 'slug', 'fields', 'success_message', 'nurture_workflow_id', 'submissions_count'];

    /** @var array<string, string> */
    protected $casts = ['fields' => 'array'];

    /** @return BelongsTo<Workflow, $this> */
    public function nurtureWorkflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'nurture_workflow_id');
    }
}
