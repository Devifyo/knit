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
            'leads.view', 'leads.manage', 'leads.convert', 'leads.notify',
            'deals.view', 'deals.manage',
            'accounts.view', 'accounts.manage',
            // Automation & CPQ
            'workflows.view', 'workflows.manage',
            'tasks.view', 'tasks.manage',
            'quotes.view', 'quotes.manage',
            // Communication
            'inbox.view', 'inbox.manage', 'chat.use',
            // Support
            'tickets.view', 'tickets.manage', 'kb.manage',
            // Marketing
            'marketing.view', 'marketing.manage',
            // Analytics
            'analytics.view', 'reports.export',
            // Collaboration & projects
            'projects.view', 'projects.manage',
            // Billing & integrations (account-level — Owner/Admin only)
            'billing.view', 'billing.manage',
            'integrations.view', 'integrations.manage',
            // Security & compliance (account-level — Owner/Admin only)
            'security.manage', 'audit.view', 'compliance.manage',
            // Industry modules — install (Owner/Admin) + use records (everyone)
            'modules.manage', 'modules.view', 'modules.use',
        ];
    }

    /** Friendly names for permission areas that don't humanize cleanly. */
    private const AREA_LABELS = [
        'kb' => 'Knowledge base',
        'crm' => 'CRM',
        'roles' => 'Roles',
    ];

    /**
     * Permissions grouped by area with human labels — drives the role editor UI.
     *
     * @return array<int, array{area: string, items: array<int, array{key: string, label: string}>}>
     */
    public static function permissionGroups(): array
    {
        $groups = [];
        foreach (self::permissions() as $perm) {
            [$area, $action] = array_pad(explode('.', $perm, 2), 2, '');
            $groups[$area][] = ['key' => $perm, 'label' => ucfirst(str_replace('_', ' ', $action ?: $perm))];
        }

        $out = [];
        foreach ($groups as $area => $items) {
            $out[] = ['area' => self::AREA_LABELS[$area] ?? ucfirst(str_replace('_', ' ', $area)), 'items' => $items];
        }

        return $out;
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
                'leads.view', 'leads.manage', 'leads.convert', 'leads.notify',
                'deals.view', 'deals.manage',
                'accounts.view', 'accounts.manage',
                'workflows.view', 'workflows.manage',
                'tasks.view', 'tasks.manage',
                'quotes.view', 'quotes.manage',
                'inbox.view', 'inbox.manage', 'chat.use',
                'tickets.view', 'tickets.manage', 'kb.manage',
                'marketing.view', 'marketing.manage',
                'analytics.view', 'reports.export',
                'projects.view', 'projects.manage',
                'modules.view', 'modules.use',
            ],
            self::AGENT => [
                'notes.view', 'notes.create',
                'contacts.view', 'contacts.manage',
                'companies.view',
                'leads.view', 'leads.manage', 'leads.convert',
                'deals.view', 'deals.manage',
                'accounts.view',
                'tasks.view', 'tasks.manage',
                'quotes.view', 'quotes.manage',
                'inbox.view', 'inbox.manage', 'chat.use',
                'tickets.view', 'tickets.manage',
                'analytics.view', 'reports.export',
                'projects.view', 'projects.manage',
                'modules.view', 'modules.use',
            ],
        ];
    }
}
