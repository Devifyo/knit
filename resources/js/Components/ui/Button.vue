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
    'inline-flex items-center justify-center gap-2 font-medium rounded-[var(--radius-control)] transition-[background-color,box-shadow,transform] duration-150 ease-[var(--ease-out-soft)] focus:outline-none focus-visible:brand-ring active:translate-y-px disabled:opacity-45 disabled:pointer-events-none select-none';

const variants = {
    primary: 'bg-brand text-white hover:brightness-95 shadow-e1',
    secondary: 'bg-surface text-ink-soft ring-1 ring-hairline ring-inset hover:bg-sunken',
    ghost: 'text-ink-soft hover:bg-sunken',
    danger: 'bg-critical text-white hover:brightness-95 shadow-e1',
};

const sizes = {
    sm: 'h-[30px] px-3 text-xs',
    md: 'h-9 px-4 text-sm',
    lg: 'h-[42px] px-5 text-md',
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
