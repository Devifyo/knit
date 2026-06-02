<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTask extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['project_id', 'parent_id', 'title', 'status', 'assigned_user_id', 'due_at', 'position'];

    /** @var array<string, string> */
    protected $casts = ['due_at' => 'date'];

    /** @return HasMany<ProjectTask, $this> */
    public function subtasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'parent_id')->orderBy('position');
    }

    /** @return HasMany<TimeEntry, $this> */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function minutesLogged(): int
    {
        return (int) $this->timeEntries()->sum('minutes');
    }
}
