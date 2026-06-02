<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit + cache log for every GeminiService result (per docs/ARCHITECTURE.md §AI).
     */
    public function up(): void
    {
        Schema::create('ai_outputs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->nullable()->index();
            $table->string('feature', 64);          // lead.score, meeting.summary, ...
            $table->nullableMorphs('entity');        // optional subject (lead/deal/ticket…)
            $table->string('prompt_hash', 64)->index();
            $table->longText('response')->nullable();
            $table->unsignedInteger('tokens')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_outputs');
    }
};
