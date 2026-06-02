<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('number');
            $table->string('subject');
            $table->text('body')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('channel')->default('email'); // email|chat|whatsapp|messenger|voice
            $table->string('status')->default('open');    // open|pending|resolved|closed
            $table->string('priority')->default('normal'); // low|normal|high|urgent
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sla_due_at')->nullable();      // first-response deadline
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // null = customer
            $table->boolean('is_internal')->default(false);
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('kb_articles', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('title');
            $table->string('slug');
            $table->text('body');
            $table->boolean('published')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_articles');
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('tickets');
    }
};
