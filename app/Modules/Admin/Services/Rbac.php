<?php

declare(strict_types=1);

namespace App\Modules\Admin\Services;

/**
 * Central definition of the system roles and permissions. Roles are seeded
 * per-tenant (spatie teams scoped by tenant_id) when a workspace is provisioned.
 * Tenants may add custom roles on top of these later.
 */
final class Rbac
{
    public const OWNER = 'Owner';

    public const ADMIN = 'Admin';

    public const MANAGER = 'Manager';

    public const AGENT = 'Agent';

    /**
     * Every permission known to the system.
     *
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'members.view',
            'members.invite',
            'members.update',
            'members.delete',
            'roles.manage',
            'settings.view',
            'settings.update',
            'branding.update',
            'notes.view',
            'notes.create',
            'notes.update',
            'notes.delete',
            // CRM
            'contacts.view', 'contacts.manage',
            'companies.view', 'companies.manage',
            'leads.view', 'leads.manage', 'leads.convert',
            'deals.view', 'deals.manage',
            'accounts.view', 'accounts.manage',
        ];
    }

    /**
     * Role → permissions map. Owner implicitly gets everything via a Gate
     * before-check, but we still attach the full set for clarity.
     *
     * @return array<string, array<int, string>>
     */
    public static function roles(): array
    {
        $all = self::permissions();

        return [
            self::OWNER => $all,
            self::ADMIN => array_values(array_diff($all, ['roles.manage'])),
            self::MANAGER => [
                'members.view',
                'settings.view',
                'notes.view', 'notes.create', 'notes.update', 'notes.delete',
                'contacts.view', 'contacts.manage',
                'companies.view', 'companies.manage',
                'leads.view', 'leads.manage', 'leads.convert',
                'deals.view', 'deals.manage',
                'accounts.view', 'accounts.manage',
            ],
            self::AGENT => [
                'notes.view', 'notes.create',
                'contacts.view', 'contacts.manage',
                'companies.view',
                'leads.view', 'leads.manage', 'leads.convert',
                'deals.view', 'deals.manage',
                'accounts.view',
            ],
        ];
    }
}
