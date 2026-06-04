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

    /** Plain-English explanation of what each permission grants (shown in the role editor). */
    private const DESCRIPTIONS = [
        'members.view' => 'See the list of workspace members and their roles.',
        'members.invite' => 'Invite people or create member accounts directly.',
        'members.update' => "Change a member's role or details.",
        'members.delete' => 'Remove members from the workspace.',
        'roles.manage' => 'Create and edit roles and choose what each one can do.',
        'settings.view' => 'Open workspace settings.',
        'settings.update' => 'Change general workspace settings.',
        'branding.update' => 'Update the workspace name, logo and brand colour.',
        'notes.view' => 'Read notes left on records.',
        'notes.create' => 'Add notes to records.',
        'notes.update' => 'Edit existing notes.',
        'notes.delete' => 'Delete notes.',
        'contacts.view' => 'View contacts and their timelines.',
        'contacts.manage' => 'Create, edit and delete contacts.',
        'companies.view' => 'View companies.',
        'companies.manage' => 'Create, edit and delete companies.',
        'leads.view' => 'View leads and the lead pipeline.',
        'leads.manage' => 'Capture, score, edit and move leads between stages.',
        'leads.convert' => 'Convert a lead into a contact, deal or project.',
        'leads.notify' => 'Get an email alert whenever a new lead is submitted.',
        'deals.view' => 'View deals and the sales pipeline.',
        'deals.manage' => 'Create, edit and move deals; manage products on a deal.',
        'accounts.view' => 'View customer accounts and renewals.',
        'accounts.manage' => 'Create and edit accounts and renewal info.',
        'workflows.view' => 'View automation workflows and their run history.',
        'workflows.manage' => 'Build, edit, enable and disable workflows.',
        'tasks.view' => 'View tasks and reminders.',
        'tasks.manage' => 'Create, complete and assign tasks.',
        'quotes.view' => 'View quotes and download quote PDFs.',
        'quotes.manage' => 'Create and edit quotes and their line items.',
        'inbox.view' => 'Read the shared team inbox.',
        'inbox.manage' => 'Reply, assign and change the status of conversations.',
        'chat.use' => 'Use the internal team chat.',
        'tickets.view' => 'View support tickets.',
        'tickets.manage' => 'Reply to, assign and resolve tickets.',
        'kb.manage' => 'Create and edit knowledge-base articles.',
        'marketing.view' => 'View campaigns and forms.',
        'marketing.manage' => 'Create and send campaigns; build capture forms.',
        'analytics.view' => 'View dashboards and reports.',
        'reports.export' => 'Export reports to CSV, Excel or PDF.',
        'projects.view' => 'View projects and their boards.',
        'projects.manage' => 'Create projects, manage tasks, time and files.',
        'billing.view' => 'View the plan, usage and invoices.',
        'billing.manage' => 'Change the subscription and apply coupons.',
        'integrations.view' => 'View webhook endpoints and deliveries.',
        'integrations.manage' => 'Add, test and remove webhook endpoints.',
        'security.manage' => 'Set the 2FA policy and IP allow-list.',
        'audit.view' => 'View the workspace audit log.',
        'compliance.manage' => 'Export or erase a contact’s data (GDPR).',
        'modules.view' => 'View installed industry modules and their records.',
        'modules.use' => 'Create and edit records in installed modules.',
        'modules.manage' => 'Install or remove industry modules.',
    ];

    /**
     * Permissions grouped by area with human labels — drives the role editor UI.
     *
     * @return array<int, array{area: string, items: array<int, array{key: string, label: string, desc: string}>}>
     */
    public static function permissionGroups(): array
    {
        $groups = [];
        foreach (self::permissions() as $perm) {
            [$area, $action] = array_pad(explode('.', $perm, 2), 2, '');
            $groups[$area][] = [
                'key' => $perm,
                'label' => ucfirst(str_replace('_', ' ', $action ?: $perm)),
                'desc' => self::DESCRIPTIONS[$perm] ?? '',
            ];
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
