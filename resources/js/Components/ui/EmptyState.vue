<script setup>
defineProps({
    title: { type: String, default: 'Nothing here yet' },
    description: { type: String, default: null },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
        <div class="mb-4 grid size-12 place-items-center rounded-[var(--radius-card)] border border-hairline bg-sunken text-muted">
            <slot name="icon">
                <svg viewBox="0 0 24 24" fill="none" class="size-6" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V7z" stroke-linejoin="round" />
                </svg>
            </slot>
        </div>
        <h3 class="text-sm font-semibold text-ink">{{ title }}</h3>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-muted">{{ description }}</p>
        <div v-if="$slots.action" class="mt-5">
            <slot name="action" />
        </div>
    </div>
</template>
