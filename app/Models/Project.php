<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia, TenantOwned
{
    /** @use HasFactory<ProjectFactory> */
    use BelongsToTenant, HasFactory, InteractsWithMedia;

    /** @var list<string> */
    protected $fillable = ['name', 'description', 'status', 'owner_id'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('files');
    }

    /** @return HasMany<ProjectTask, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class)->whereNull('parent_id')->orderBy('position');
    }

    /** @return HasMany<ProjectTask, $this> */
    public function allTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
