<script setup>
import { computed } from 'vue';

const props = defineProps({
    color: { type: String, default: 'gray' }, // gray | brand | green | yellow | red | blue
    size: { type: String, default: 'md' }, // sm | md
});

const colors = {
    gray: 'bg-gray-100 text-gray-700 ring-gray-200',
    brand: 'bg-brand-50 text-brand-700 ring-brand-200',
    green: 'bg-green-50 text-green-700 ring-green-200',
    yellow: 'bg-yellow-50 text-yellow-700 ring-yellow-200',
    red: 'bg-red-50 text-red-700 ring-red-200',
    blue: 'bg-blue-50 text-blue-700 ring-blue-200',
};

const classes = computed(() => [
    'inline-flex items-center gap-1 rounded-full font-medium ring-1 ring-inset',
    colors[props.color] ?? colors.gray,
    props.size === 'sm' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-1 text-xs',
]);
</script>

<template>
    <span :class="classes"><slot /></span>
</template>
