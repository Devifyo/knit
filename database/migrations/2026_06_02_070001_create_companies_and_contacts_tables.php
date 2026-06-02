<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('name');
            $table->string('domain')->nullable();
            $table->string('industry')->nullable();
            $table->string('size')->nullable();
            $table->unsignedBigInteger('annual_revenue')->nullable(); // minor units
            $table->json('address')->nullable();
            $table->foreignId('parent_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->unsignedTinyInteger('health_score')->default(50);
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('job_title')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('lifecycle_stage')->default('lead'); // subscriber|lead|mql|sql|customer|evangelist
            $table->string('source')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('social_profiles')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('companies');
    }
};
