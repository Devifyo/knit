<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Lead extends Model implements AuditableContract, TenantOwned
{
    /** @use HasFactory<LeadFactory> */
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'name', 'email', 'phone', 'source', 'status', 'score',
        'assigned_user_id', 'pipeline_id', 'converted_to_contact_id',
        'converted_at', 'custom_fields',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'custom_fields' => 'array',
        'converted_at' => 'datetime',
        'score' => 'integer',
    ];

    public function isConverted(): bool
    {
        return $this->converted_at !== null;
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /** @return BelongsTo<Contact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'converted_to_contact_id');
    }

    /** @return MorphMany<Activity, $this> */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')->latest();
    }
}
