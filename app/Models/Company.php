<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model implements TenantOwned
{
    /** @use HasFactory<CompanyFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'name', 'domain', 'industry', 'size', 'annual_revenue',
        'address', 'parent_company_id', 'health_score', 'owner_id', 'custom_fields',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'address' => 'array',
        'custom_fields' => 'array',
        'health_score' => 'integer',
    ];

    /** @return BelongsTo<Company, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'parent_company_id');
    }

    /** @return HasMany<Contact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /** @return HasMany<Deal, $this> */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /** @return HasOne<Account, $this> */
    public function account(): HasOne
    {
        return $this->hasOne(Account::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return MorphToMany<Tag, $this> */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
