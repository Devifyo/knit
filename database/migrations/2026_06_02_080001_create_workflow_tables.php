<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('name');
            $table->string('trigger_event'); // lead.created | deal.stage_changed | contact.created ...
            $table->json('trigger_config')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->string('type'); // wait|send_email|create_task|update_field|add_tag|assign_owner|webhook|condition
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_runs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->morphs('subject');
            $table->string('status')->default('running'); // running|waiting|completed|failed|stopped
            $table->unsignedInteger('current_step')->default(0);
            $table->json('context')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_run_steps', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->foreignId('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
            $table->foreignId('workflow_step_id')->constrained('workflow_steps')->cascadeOnDelete();
            $table->string('status'); // done|skipped|failed
            $table->json('output')->nullable();
            $table->timestamp('ran_at')->nullable();
            $table->timestamps();
            $table->unique(['workflow_run_id', 'workflow_step_id'], 'run_step_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_run_steps');
        Schema::dropIfExists('workflow_runs');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflows');
    }
};
