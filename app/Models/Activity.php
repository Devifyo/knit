<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['type', 'body', 'due_at', 'completed_at', 'user_id', 'subject_type', 'subject_id'];

    /** @var array<string, string> */
    protected $casts = ['due_at' => 'datetime', 'completed_at' => 'datetime'];

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
