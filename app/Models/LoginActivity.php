<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A successful-login record (device/session tracking). Not tenant-scoped via the
 * global scope because logins are recorded with an explicit tenant_id and read
 * back filtered by user — keeping it independent of tenant initialization order.
 */
class LoginActivity extends Model
{
    /** @var list<string> */
    protected $fillable = ['tenant_id', 'user_id', 'ip_address', 'user_agent', 'logged_in_at'];

    /** @var array<string, string> */
    protected $casts = ['logged_in_at' => 'datetime'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
