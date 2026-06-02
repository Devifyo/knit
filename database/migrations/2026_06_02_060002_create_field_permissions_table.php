<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-tenant, per-role field-level permissions. A row grants a role the
     * right to view and/or edit a specific field on a specific entity. Absence
     * of a row means "use the role default" (resolved by FieldPermissionService).
     */
    public function up(): void
    {
        Schema::create('field_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('role', 64);            // role name (e.g. Agent)
            $table->string('entity', 64);          // e.g. contact, deal
            $table->string('field_key', 100);      // e.g. annual_revenue
            $table->boolean('can_view')->default(true);
            $table->boolean('can_edit')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'role', 'entity', 'field_key'], 'field_perm_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_permissions');
    }
};
