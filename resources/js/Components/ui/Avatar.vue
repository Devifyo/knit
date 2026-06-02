<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, default: '?' },
    src: { type: String, default: null },
    size: { type: String, default: 'md' }, // sm | md | lg
});

const sizes = { sm: 'size-6 text-[10px]', md: 'size-8 text-xs', lg: 'size-10 text-sm' };

const initials = computed(() =>
    (props.name || '?')
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
            'inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full font-semibold ring-1 ring-black/5',
            'brand-wash text-[var(--brand)]',
            sizes[size],
        ]"
    >
        <img v-if="src" :src="src" :alt="name" class="size-full object-cover" />
        <span v-else>{{ initials }}</span>
    </span>
</template>
