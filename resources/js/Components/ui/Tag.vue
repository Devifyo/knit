<script setup>
import { computed } from 'vue';

const props = defineProps({
    color: { type: String, default: 'neutral' }, // neutral | brand | positive | warning | critical | info
    size: { type: String, default: 'md' }, // sm | md
    dot: { type: Boolean, default: false },
});

const colors = {
    neutral: 'bg-sunken text-ink-soft ring-hairline',
    brand: 'brand-wash text-[var(--brand)] ring-transparent',
    positive: 'bg-positive/10 text-positive ring-positive/15',
    warning: 'bg-warning/10 text-warning ring-warning/15',
    critical: 'bg-critical/10 text-critical ring-critical/15',
    info: 'bg-info/10 text-info ring-info/15',
};
const dotColor = {
    neutral: 'bg-faint', brand: 'bg-[var(--brand)]', positive: 'bg-positive',
    warning: 'bg-warning', critical: 'bg-critical', info: 'bg-info',
};

const classes = computed(() => [
    'inline-flex items-center gap-1.5 rounded-full font-medium ring-1 ring-inset whitespace-nowrap',
    colors[props.color] ?? colors.neutral,
    props.size === 'sm' ? 'px-2 py-0.5 text-[11px]' : 'px-2.5 py-1 text-xs',
]);
</script>

<template>
    <span :class="classes">
        <span v-if="dot" class="size-1.5 rounded-full" :class="dotColor[color]" />
        <slot />
    </span>
</template>
