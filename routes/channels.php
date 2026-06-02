<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels (Reverb)
|--------------------------------------------------------------------------
|
| Channel map per docs/ARCHITECTURE.md §Real-time. Every channel is scoped
| to a tenant; authorization callbacks confirm the authenticated user
| belongs to {tenantId} before granting access. Per-permission checks are
| layered on in Phase 1 once RBAC lands.
|
*/

Broadcast::channel('tenant.{tenantId}.notifications', function ($user, string $tenantId) {
    return userBelongsToTenant($user, $tenantId);
});

Broadcast::channel('tenant.{tenantId}.pipeline.{pipelineId}', function ($user, string $tenantId, string $pipelineId) {
    return userBelongsToTenant($user, $tenantId);
});

Broadcast::channel('tenant.{tenantId}.inbox.{userId}', function ($user, string $tenantId, string $userId) {
    return userBelongsToTenant($user, $tenantId) && (string) $user->id === $userId;
});

// Shared-inbox live updates (whole workspace).
Broadcast::channel('tenant.{tenantId}.inbox', function ($user, string $tenantId) {
    return userBelongsToTenant($user, $tenantId);
});

// Team chat presence — returns member identity for the online roster.
Broadcast::channel('tenant.{tenantId}.chat', function ($user, string $tenantId) {
    return userBelongsToTenant($user, $tenantId)
        ? ['id' => $user->id, 'name' => $user->name]
        : false;
});

Broadcast::channel('tenant.{tenantId}.dashboard', function ($user, string $tenantId) {
    return userBelongsToTenant($user, $tenantId);
});

Broadcast::channel('presence-tenant.{tenantId}.chat.{channelId}', function ($user, string $tenantId, string $channelId) {
    if (! userBelongsToTenant($user, $tenantId)) {
        return false;
    }

    return ['id' => $user->id, 'name' => $user->name];
});

/**
 * Confirm a user belongs to the given tenant. Compares the user's own
 * tenant_id, so it works on the /broadcasting/auth route without depending on
 * the tenant-resolution middleware having run.
 *
 * Guarded because channels.php is re-required each time the app boots (e.g.
 * once per test), which would otherwise redeclare the function.
 */
if (! function_exists('userBelongsToTenant')) {
    function userBelongsToTenant($user, string $tenantId): bool
    {
        return $user !== null && (string) $user->tenant_id === $tenantId;
    }
}
