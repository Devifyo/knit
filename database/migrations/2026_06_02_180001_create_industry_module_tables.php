<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Installable industry modules. `module_settings` is the per-tenant on/off
     * switch; `module_records` holds the manifest-driven records for whichever
     * modules a workspace has enabled (Real Estate properties, candidates, …).
     */
    public function up(): void
    {
        Schema::create('module_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('key');
            $table->boolean('enabled')->default(false);
            $table->timestamps();
            $table->unique(['tenant_id', 'key']);
        });

        Schema::create('module_records', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('module_key');
            $table->string('entity_key');
            $table->string('title');
            $table->string('status')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'module_key', 'entity_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_records');
        Schema::dropIfExists('module_settings');
    }
};
