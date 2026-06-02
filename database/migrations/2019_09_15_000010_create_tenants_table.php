<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // Workspace identity + white-label branding (real, queryable columns).
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand_color', 9)->default('#4f46e5');
            $table->string('logo_path')->nullable();
            $table->string('timezone')->default('UTC');
            $table->boolean('ai_enabled')->default(true);

            $table->timestamps();
            $table->json('data')->nullable(); // VirtualColumn overflow (extra settings)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
