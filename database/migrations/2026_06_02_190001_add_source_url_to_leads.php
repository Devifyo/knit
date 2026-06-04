<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Where a lead came from — the capture form / landing URL it was submitted through. */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('source_url')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('source_url');
        });
    }
};
