import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Permission helpers backed by spatie/laravel-permission.
 *
 * Phase 1 shares `auth.user.permissions` and `auth.user.roles` from the
 * Inertia middleware; until then these resolve to safe empty defaults.
 */
export function usePermissions() {
    const page = usePage();

    const permissions = computed(() => page.props.auth?.user?.permissions ?? []);
    const roles = computed(() => page.props.auth?.user?.roles ?? []);

    const can = (permission) => permissions.value.includes(permission);
    const hasRole = (role) => roles.value.includes(role);
    const hasAnyRole = (...names) => names.some(hasRole);

    return { permissions, roles, can, hasRole, hasAnyRole };
}
