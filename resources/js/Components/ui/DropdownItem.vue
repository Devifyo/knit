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
            :class="['flex w-full items-center gap-2 rounded-[6px] px-3 py-2 text-left text-sm transition-colors', active ? 'bg-sunken text-ink' : 'text-ink-soft']"
        >
            <slot />
        </component>
    </MenuItem>
</template>
