<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ChatMessage;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Invitation;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Pipeline;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Task;
use App\Models\User;
use App\Models\Workflow;
use App\Modules\Admin\Services\Rbac;
use App\Modules\Admin\Services\WorkspaceProvisioner;
use App\Modules\Communication\Services\InboundEmailService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

            // Phase 3 — products, a follow-up workflow, a sample quote, tasks.
            $products = Product::factory()->count(5)->create();

            $wf = Workflow::create(['name' => 'New lead follow-up', 'trigger_event' => 'lead.created', 'enabled' => true]);
            foreach ([
                ['type' => 'wait', 'config' => ['days' => 1]],
                ['type' => 'send_email', 'config' => ['to_field' => 'email', 'subject' => 'Thanks for your interest', 'body' => 'Following up on your enquiry.']],
                ['type' => 'condition', 'config' => ['condition' => ['operator' => 'and', 'rules' => [['field' => 'status', 'op' => 'equals', 'value' => 'new']]]]],
                ['type' => 'create_task', 'config' => ['title' => 'Call lead — no reply', 'due_in_days' => 1, 'assign_to_field' => 'assigned_user_id']],
            ] as $i => $step) {
                $wf->steps()->create([...$step, 'order' => $i]);
            }

            $quote = Quote::create([
                'number' => 'Q-'.now()->format('Ymd').'-0001', 'currency' => 'USD', 'tax_rate' => 8.5,
                'status' => 'draft', 'created_by' => $owner->id,
            ]);
            foreach ($products->take(2)->values() as $pos => $product) {
                $quote->items()->create([
                    'product_id' => $product->id, 'name' => $product->name,
                    'quantity' => $pos + 1, 'unit_price' => $product->unit_price, 'discount_pct' => $pos * 5, 'position' => $pos,
                ]);
            }

            Task::factory()->count(4)->create(['assigned_user_id' => $owner->id, 'created_by' => $owner->id]);

            Invitation::create([
                'email' => 'teammate@'.$tenant->slug.'.test',
                'role' => 'Manager',
                'token' => Str::random(48),
                'invited_by' => $owner->id,
                'expires_at' => now()->addDays(7),
            ]);

            // Phase 4 — a threaded inbox conversation linked to a contact + team chat.
            $contact = Contact::whereNotNull('email')->first();
            if ($contact) {
                $svc = app(InboundEmailService::class);
                $msg = $svc->handle([
                    'from_email' => $contact->email, 'from_name' => $contact->first_name,
                    'subject' => 'Interested in a demo', 'body' => "Hi, we'd love a demo of your product. When are you free?",
                    'message_id' => (string) Str::uuid(),
                ]);
                $svc->handle([
                    'from_email' => $contact->email, 'from_name' => $contact->first_name,
                    'subject' => 'Re: Interested in a demo', 'body' => 'Following up — also, do you offer annual billing?',
                    'message_id' => (string) Str::uuid(), 'in_reply_to' => $msg->external_id,
                ]);
            }

            ChatMessage::create(['user_id' => $owner->id, 'body' => "Morning team — let's move those deals forward today."]);

            tenancy()->end();
        }
    }
}
