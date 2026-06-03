<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Security\IpMatcher;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * A workspace. We run single-database scoped tenancy, so this does NOT use the
 * database-provisioning concerns — only domain resolution + branding/settings.
 *
 * Branding columns (name, slug, brand_color, logo_path, timezone, ai_enabled)
 * are real columns; anything else set on the model overflows into the `data`
 * JSON column via Stancl's VirtualColumn.
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $brand_color
 * @property string|null $logo_path
 * @property string $timezone
 * @property bool $ai_enabled
 * @property string|null $smtp_host
 * @property int|null $smtp_port
 * @property string|null $smtp_username
 * @property string|null $smtp_password
 * @property string|null $smtp_encryption
 * @property string|null $smtp_from_address
 * @property string|null $smtp_from_name
 */
class Tenant extends BaseTenant
{
    /** @use HasFactory<TenantFactory> */
    use HasDomains, HasFactory;

    /**
     * Real (non-virtual) columns. Everything else is stored in `data`.
     *
     * @return array<int, string>
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'brand_color',
            'logo_path',
            'timezone',
            'ai_enabled',
            'require_2fa',
            'allowed_ips',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ai_enabled' => 'boolean',
            'require_2fa' => 'boolean',
            'allowed_ips' => 'array',
            // Stored (encrypted) in the `data` JSON column via VirtualColumn.
            'smtp_password' => 'encrypted',
        ];
    }

    /** Whether this workspace has its own SMTP configured (else env fallback). */
    public function hasCustomMail(): bool
    {
        return filled($this->smtp_host);
    }

    /**
     * Whether the given client IP is permitted. An empty allow-list permits all.
     */
    public function ipAllowed(string $ip): bool
    {
        $list = $this->getAttribute('allowed_ips');
        if (! is_array($list) || $list === []) {
            return true;
        }

        foreach ($list as $rule) {
            if (IpMatcher::matches($ip, (string) $rule)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Users that belong to this workspace.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Branding payload shared with the frontend for white-labeling.
     *
     * @return array<string, mixed>
     */
    public function branding(): array
    {
        return [
            'name' => $this->name,
            'brand_color' => $this->brand_color,
            'logo_path' => $this->logo_path ? "/storage/{$this->logo_path}" : null,
        ];
    }
}
