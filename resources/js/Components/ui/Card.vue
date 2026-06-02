<script setup>
defineProps({
    title: { type: String, default: null },
    subtitle: { type: String, default: null },
    flush: { type: Boolean, default: false }, // remove body padding (e.g. for tables)
});
</script>

<template>
    <div class="rounded-[var(--radius-card)] border border-hairline bg-surface shadow-e1">
        <div v-if="title || $slots.header" class="flex items-center justify-between gap-3 border-b border-hairline-soft px-5 py-4">
            <div class="min-w-0">
                <h3 v-if="title" class="truncate text-sm font-semibold tracking-[-0.01em] text-ink">{{ title }}</h3>
                <p v-if="subtitle" class="mt-0.5 truncate text-xs text-muted">{{ subtitle }}</p>
            </div>
            <slot name="header" />
        </div>
        <div :class="flush ? '' : 'p-5'">
            <slot />
        </div>
        <div v-if="$slots.footer" class="border-t border-hairline-soft px-5 py-3">
            <slot name="footer" />
        </div>
    </div>
</template>
