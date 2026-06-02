import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';

/**
 * Server-driven table state: search, sort, filters, pagination.
 * Pushes changes to the current Inertia route, preserving state & scroll.
 */
export function useTable(routeName, initial = {}) {
    const state = reactive({
        search: '',
        sort: null,
        direction: 'asc',
        filters: {},
        page: 1,
        ...initial,
    });

    const reload = () => {
        router.get(route(routeName), { ...state, filters: undefined, ...state.filters }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    watchDebounced(() => state.search, reload, { debounce: 300 });
    watch([() => state.sort, () => state.direction, () => state.page, () => state.filters], reload, { deep: true });

    return { state, reload };
}
