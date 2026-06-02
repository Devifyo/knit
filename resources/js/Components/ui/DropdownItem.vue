<script setup>
import { computed } from 'vue';
import { MenuItem } from '@headlessui/vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, default: null },
    as: { type: String, default: 'button' },
});

const component = computed(() => (props.href ? Link : props.as));
</script>

<template>
    <MenuItem v-slot="{ active }">
        <component
            :is="component"
            :href="href"
            :class="[
                'flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm',
                active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
            ]"
        >
            <slot />
        </component>
    </MenuItem>
</template>
