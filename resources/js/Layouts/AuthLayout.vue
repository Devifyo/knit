<script setup>
defineProps({
    title: { type: String, default: null },
    subtitle: { type: String, default: null },
});
</script>

<template>
    <div class="flex min-h-[100dvh] items-center justify-center bg-canvas px-4 py-12">
        <div class="w-full max-w-[400px]">
            <div class="mb-8 flex flex-col items-center gap-3">
                <span class="grid size-10 place-items-center rounded-xl text-lg font-bold text-white" :style="{ background: 'var(--brand)' }">K</span>
                <div class="text-center">
                    <h1 v-if="title" class="text-lg font-semibold tracking-[-0.02em] text-ink">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-1 text-sm text-muted">{{ subtitle }}</p>
                </div>
            </div>
            <div class="rounded-2xl border border-hairline bg-surface p-7 shadow-e1">
                <slot />
            </div>
        </div>
    </div>
</template>
