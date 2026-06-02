<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use App\Modules\Admin\Services\Rbac;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed two demo workspaces so cross-tenant isolation is visible in the UI.
     */
    public function run(): void
    {
        $provisioner = app(WorkspaceProvisioner::class);

        $acme = $provisioner->provision([
            'name' => 'Acme Inc.',
            'owner_name' => 'Ada Owner',
            'email' => 'owner@acme.test',
            'password' => 'password',
        ]);

        $globex = $provisioner->provision([
            'name' => 'Globex',
            'owner_name' => 'Greg Owner',
            'email' => 'owner@globex.test',
            'password' => 'password',
        ]);

        // An Agent in Acme to demonstrate RBAC denial.
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($acme->getTenantKey());
        $agent = User::create([
            'tenant_id' => $acme->getTenantKey(),
            'name' => 'Andy Agent',
            'email' => 'agent@acme.test',
            'password' => Hash::make('password'),
        ]);
        $agent->assignRole(Rbac::AGENT);

        // A few notes per workspace (tenant_id auto-filled while tenant active).
        foreach ([$acme, $globex] as $tenant) {
            tenancy()->initialize($tenant);
            $owner = $tenant->users()->first();
            Note::factory()->count(3)->create(['user_id' => $owner->id]);
            tenancy()->end();
        }
    }
}
