<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @var list<string> */
    protected $fillable = ['name', 'subject', 'subject_b', 'body', 'cta_label', 'cta_url', 'audience', 'status', 'sent_at'];

    /** @var array<string, string> */
    protected $casts = ['sent_at' => 'datetime'];

    /** @return HasMany<CampaignRecipient, $this> */
    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function isAbTest(): bool
    {
        return filled($this->subject_b);
    }
}
