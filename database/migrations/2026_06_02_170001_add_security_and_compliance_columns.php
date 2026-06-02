<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Security & compliance columns: per-tenant 2FA enforcement + IP allow-list,
     * and a GDPR erasure timestamp on contacts.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('require_2fa')->default(false)->after('ai_enabled');
            $table->json('allowed_ips')->nullable()->after('require_2fa');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->timestamp('anonymized_at')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['require_2fa', 'allowed_ips']);
        });
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('anonymized_at');
        });
    }
};
