<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A tenant-owned workspace note. Primary purpose in Phase 1: prove cross-tenant
 * isolation end-to-end. Also the first real consumer of {@see BelongsToTenant}.
 *
 * @property int $id
 * @property string $tenant_id
 * @property int $user_id
 * @property string $title
 * @property string|null $body
 */
class Note extends Model implements AuditableContract, TenantOwned
{
    /** @use HasFactory<NoteFactory> */
    use Auditable, BelongsToTenant, HasFactory, LogsActivity, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'body',
        'user_id',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'body'])
            ->logOnlyDirty();
    }
}
