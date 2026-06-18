<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Public marketing-site consultation requests, including an auditable record of
 * SMS opt-in consent (the exact text shown, IP, user-agent and timestamp) for
 * toll-free / A2P SMS verification. Not tenant-scoped — these are inbound leads
 * to the company itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_submissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->text('requirements')->nullable();
            $table->boolean('sms_consent')->default(false);
            $table->text('consent_text')->nullable();
            $table->string('consent_ip')->nullable();
            $table->string('consent_user_agent', 1024)->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');
    }
};
