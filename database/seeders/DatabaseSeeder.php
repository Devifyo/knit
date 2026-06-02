<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Pipeline;
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

        // Demo data per workspace (tenant_id auto-filled while tenant active).
        foreach ([$acme, $globex] as $tenant) {
            tenancy()->initialize($tenant);
            $owner = $tenant->users()->first();

            Note::factory()->count(3)->create(['user_id' => $owner->id]);

            $companies = Company::factory()->count(6)->create(['owner_id' => $owner->id]);
            Contact::factory()->count(18)->create([
                'owner_id' => $owner->id,
                'company_id' => fn () => $companies->random()->id,
            ]);
            Lead::factory()->count(8)->create(['assigned_user_id' => $owner->id]);

            // Seed deals across the default pipeline's stages.
            $pipeline = Pipeline::where('is_default', true)->first();
            $stages = $pipeline->stages;
            Contact::all()->take(10)->each(function ($contact) use ($pipeline, $stages, $owner) {
                $stage = $stages->random();
                Deal::factory()->create([
                    'pipeline_id' => $pipeline->id,
                    'stage_id' => $stage->id,
                    'probability' => $stage->probability,
                    'contact_id' => $contact->id,
                    'company_id' => $contact->company_id,
                    'owner_id' => $owner->id,
                    'status' => $stage->type === 'won' ? 'won' : ($stage->type === 'lost' ? 'lost' : 'open'),
                ]);
            });

            // Accounts for a couple of companies.
            $companies->take(3)->each(fn ($c) => Account::create([
                'company_id' => $c->id,
                'health_score' => $c->health_score,
                'renewal_date' => now()->addMonths(rand(1, 10)),
                'renewal_status' => 'upcoming',
            ]));

            tenancy()->end();
        }
    }
}
