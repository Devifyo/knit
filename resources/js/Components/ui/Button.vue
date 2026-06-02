<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    variant: { type: String, default: 'primary' }, // primary | secondary | ghost | danger
    size: { type: String, default: 'md' }, // sm | md | lg
    type: { type: String, default: 'button' },
    href: { type: String, default: null },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
});

const base =
    'inline-flex items-center justify-center gap-2 font-medium rounded-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';

const variants = {
    primary: 'bg-brand-600 text-white hover:bg-brand-700 shadow-sm',
    secondary: 'bg-white text-gray-800 border border-gray-300 hover:bg-gray-50 shadow-sm',
    ghost: 'text-gray-700 hover:bg-gray-100',
    danger: 'bg-red-600 text-white hover:bg-red-700 shadow-sm',
};

const sizes = {
    sm: 'text-xs px-2.5 py-1.5',
    md: 'text-sm px-4 py-2',
    lg: 'text-base px-5 py-2.5',
};

const classes = computed(() => [base, variants[props.variant], sizes[props.size]]);
const component = computed(() => (props.href ? Link : 'button'));
</script>

<template>
    <component
        :is="component"
        :href="href"
        :type="href ? undefined : type"
        :disabled="disabled || loading"
        :class="classes"
    >
        <svg v-if="loading" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
        </svg>
        <slot />
    </component>
</template>
