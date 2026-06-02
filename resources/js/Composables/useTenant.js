import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Access the current workspace (tenant) shared by HandleInertiaRequests.
 */
export function useTenant() {
    const page = usePage();
    const tenant = computed(() => page.props.tenant ?? null);
    const isCentral = computed(() => tenant.value === null);

    return { tenant, isCentral };
}
