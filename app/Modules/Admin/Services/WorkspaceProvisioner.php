<?php

declare(strict_types=1);

namespace App\Modules\Admin\Services;

use App\Models\CustomFieldDefinition;
use App\Models\Pipeline;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Provisions a new workspace (tenant): the tenant record + its subdomain, the
 * full role/permission set scoped to that tenant, and the owner user.
 *
 * Default pipelines, ticket statuses and email templates are seeded by their
 * respective modules in Phase 2/5 (those tables don't exist yet).
 */
class WorkspaceProvisioner
{
    /**
     * @param  array{name:string, owner_name:string, email:string, password:string}  $data
     */
    public function provision(array $data): Tenant
    {
        return DB::transaction(function () use ($data): Tenant {
            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
            ]);

            // Subdomain domain record (e.g. acme.localhost).
            $central = config('tenancy.central_domains.1', 'localhost');
            $tenant->domains()->create([
                'domain' => $tenant->slug.'.'.$central,
            ]);

            $this->seedRolesAndPermissions($tenant);

            $owner = $this->createOwner($tenant, $data);

            $this->seedCrmDefaults($tenant);

            return $tenant->setRelation('owner', $owner);
        });
    }

    public function seedRolesAndPermissions(Tenant $tenant): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($tenant->getTenantKey());

        // Permissions are global (not team-scoped); create once and keep the
        // model instances so we can sync by model (not by name). Syncing by
        // model avoids spatie's cache-backed name lookup, which can be stale
        // immediately after creation in a fresh (test) database.
        $permissions = collect(Rbac::permissions())
            ->mapWithKeys(fn (string $name) => [$name => Permission::findOrCreate($name, 'web')]);

        $registrar->forgetCachedPermissions();

        foreach (Rbac::roles() as $roleName => $names) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions->only($names)->values()->all());
        }

        $registrar->forgetCachedPermissions();
    }

    /**
     * @param  array{owner_name:string, email:string, password:string}  $data
     */
    protected function createOwner(Tenant $tenant, array $data): User
    {
        $user = User::create([
            'tenant_id' => $tenant->getTenantKey(),
            'name' => $data['owner_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());
        $user->assignRole(Rbac::OWNER);

        return $user;
    }

    /**
     * Seed the default sales pipeline + stages and a starter custom field.
     */
    public function seedCrmDefaults(Tenant $tenant): void
    {
        tenancy()->initialize($tenant);

        try {
            $pipeline = Pipeline::create([
                'name' => 'Sales Pipeline',
                'type' => 'deal',
                'is_default' => true,
            ]);

            $stages = [
                ['name' => 'New', 'probability' => 10, 'type' => 'open'],
                ['name' => 'Qualified', 'probability' => 30, 'type' => 'open'],
                ['name' => 'Proposal', 'probability' => 60, 'type' => 'open'],
                ['name' => 'Negotiation', 'probability' => 80, 'type' => 'open'],
                ['name' => 'Won', 'probability' => 100, 'type' => 'won'],
                ['name' => 'Lost', 'probability' => 0, 'type' => 'lost'],
            ];
            foreach ($stages as $i => $stage) {
                $pipeline->stages()->create([...$stage, 'order' => $i]);
            }

            CustomFieldDefinition::create([
                'entity' => 'contact',
                'key' => 'linkedin',
                'label' => 'LinkedIn',
                'type' => 'text',
                'order' => 0,
            ]);
        } finally {
            tenancy()->end();
        }
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'workspace';
        $slug = $base;
        $i = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
