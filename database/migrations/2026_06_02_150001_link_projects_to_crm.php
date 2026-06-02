<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wire projects into the CRM graph the way popular CRMs do: a delivery
     * project hangs off the (won) Deal and inherits that deal's customer —
     * Company + Contact. All nullable so internal projects can have no deal.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('deal_id')->nullable()->after('description')->constrained('deals')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->after('deal_id')->constrained('companies')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->after('company_id')->constrained('contacts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deal_id');
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('contact_id');
        });
    }
};
