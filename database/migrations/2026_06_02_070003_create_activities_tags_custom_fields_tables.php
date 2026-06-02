<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedTinyInteger('health_score')->default(50);
            $table->date('renewal_date')->nullable();
            $table->string('renewal_status')->nullable(); // upcoming|renewed|churned
            $table->timestamps();
        });

        // Polymorphic unified timeline.
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('type'); // note|call|email|meeting|task|system
            $table->morphs('subject'); // subject_type + subject_id
            $table->text('body')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('name');
            $table->string('color', 9)->default('#71717a');
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->morphs('taggable');
            $table->primary(['tag_id', 'taggable_id', 'taggable_type'], 'taggables_primary');
        });

        // Per-tenant custom field definitions (values live in each entity's
        // custom_fields json column).
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('entity', 32); // contact|company|lead|deal
            $table->string('key', 64);
            $table->string('label');
            $table->string('type', 32)->default('text'); // text|number|date|select|boolean
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'entity', 'key'], 'cfd_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_definitions');
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('accounts');
    }
};
