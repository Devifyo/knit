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
 * Confirm a user belongs to the given tenant.
 *
 * Phase 0 stub: returns true when a tenant context is active and matches.
 * Phase 1 replaces this with a real membership lookup once the Tenant <-> User
 * relationship and BelongsToTenant scoping are in place.
 *
 * Guarded because channels.php is re-required each time the app boots (e.g. once
 * per test), which would otherwise redeclare the function.
 */
if (! function_exists('userBelongsToTenant')) {
    function userBelongsToTenant($user, string $tenantId): bool
    {
        $current = function_exists('tenant') ? tenant() : null;

        return $user !== null && $current !== null
            && (string) $current->getTenantKey() === $tenantId;
    }
}
