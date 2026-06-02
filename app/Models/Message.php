<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = [
        'conversation_id', 'direction', 'is_internal', 'from_name', 'from_email',
        'to_email', 'body', 'external_id', 'in_reply_to', 'user_id', 'read_at',
    ];

    /** @var array<string, string> */
    protected $casts = ['is_internal' => 'boolean', 'read_at' => 'datetime'];

    /** @return BelongsTo<Conversation, $this> */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
