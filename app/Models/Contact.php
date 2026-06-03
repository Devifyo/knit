<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Database\Factories\ContactFactory;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Contact extends Model implements AuditableContract, AuthenticatableContract, TenantOwned
{
    /** @use HasFactory<ContactFactory> */
    use Auditable, AuthenticatableTrait, BelongsToTenant, HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'job_title',
        'company_id', 'lifecycle_stage', 'source', 'owner_id',
        'social_profiles', 'custom_fields', 'anonymized_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'social_profiles' => 'array',
        'custom_fields' => 'array',
        'anonymized_at' => 'datetime',
        'portal_enabled' => 'boolean',
        'portal_activated_at' => 'datetime',
        'portal_last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /** @var list<string> */
    protected $hidden = ['password', 'portal_token', 'remember_token'];

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }

    /** Can this contact sign in to the customer portal right now? */
    public function canUsePortal(): bool
    {
        return $this->portal_enabled && $this->portal_activated_at !== null;
    }

    /** @return HasMany<Ticket, $this> */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class)->latest();
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<Deal, $this> */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class)->latest();
    }

    /** @return MorphMany<Activity, $this> */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')->latest();
    }

    /** @return MorphToMany<Tag, $this> */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
