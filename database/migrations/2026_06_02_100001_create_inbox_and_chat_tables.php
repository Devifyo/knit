<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Shared-inbox conversation (a thread).
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('subject');
            $table->string('channel')->default('email'); // email|chat|whatsapp...
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('open'); // open|closed|snoozed
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('direction')->default('inbound'); // inbound|outbound
            $table->boolean('is_internal')->default(false);   // internal note vs customer-facing
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('to_email')->nullable();
            $table->text('body');
            $table->string('external_id')->nullable()->index(); // email Message-ID
            $table->string('in_reply_to')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // author (outbound/internal)
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Team chat (Reverb presence).
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
