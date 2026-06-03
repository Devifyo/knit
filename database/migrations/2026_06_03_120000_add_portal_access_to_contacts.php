<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer portal access for contacts: lets a contact log in (email + password)
 * to a branded self-service portal scoped to their own records.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->string('password')->nullable()->after('email');
            $table->boolean('portal_enabled')->default(false)->after('password');
            $table->string('portal_token', 64)->nullable()->index()->after('portal_enabled');
            $table->timestamp('portal_activated_at')->nullable()->after('portal_token');
            $table->timestamp('portal_last_login_at')->nullable()->after('portal_activated_at');
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropColumn([
                'password', 'portal_enabled', 'portal_token',
                'portal_activated_at', 'portal_last_login_at', 'remember_token',
            ]);
        });
    }
};
