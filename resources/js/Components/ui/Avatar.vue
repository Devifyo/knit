<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, default: '?' },
    src: { type: String, default: null },
    size: { type: String, default: 'md' }, // sm | md | lg
});

const sizes = { sm: 'size-7 text-xs', md: 'size-9 text-sm', lg: 'size-12 text-base' };

const initials = computed(() =>
    props.name
        .split(' ')
        .map((p) => p[0])
        .filter(Boolean)
        .slice(0, 2)
        .join('')
        .toUpperCase(),
);
</script>

<template>
    <span
        :class="[
            'inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-100 font-semibold text-brand-700',
            sizes[size],
        ]"
    >
        <img v-if="src" :src="src" :alt="name" class="size-full object-cover" />
        <span v-else>{{ initials }}</span>
    </span>
</template>
