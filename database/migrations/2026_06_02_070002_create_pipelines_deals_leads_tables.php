<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('name');
            $table->string('type')->default('deal'); // deal | lead_nurture
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->foreignId('pipeline_id')->constrained('pipelines')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->unsignedTinyInteger('probability')->default(0);
            $table->string('type')->default('open'); // open | won | lost
            $table->timestamps();
        });

        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('name');
            $table->foreignId('pipeline_id')->constrained('pipelines')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('stages')->cascadeOnDelete();
            $table->unsignedBigInteger('amount')->default(0); // minor units
            $table->char('currency', 3)->default('USD');
            $table->unsignedTinyInteger('probability')->nullable(); // overrides stage
            $table->date('expected_close_date')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('open'); // open | won | lost
            $table->unsignedInteger('board_order')->default(0);
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('new'); // new|working|qualified|unqualified
            $table->unsignedInteger('score')->default(0);
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pipeline_id')->nullable()->constrained('pipelines')->nullOnDelete();
            $table->foreignId('converted_to_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('stages');
        Schema::dropIfExists('pipelines');
    }
};
