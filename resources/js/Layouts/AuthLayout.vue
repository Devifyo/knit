<script setup>
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: null },
    subtitle: { type: String, default: null },
});

const appName = usePage().props.appName || 'Knit';
</script>

<template>
    <div class="relative flex min-h-[100dvh] flex-col overflow-hidden bg-canvas px-4 py-8">
        <!-- soft brand glow backdrop (no patterns) -->
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[460px]"
             style="background: radial-gradient(58% 100% at 50% 0%, color-mix(in srgb, var(--brand) 16%, transparent), transparent 72%);" />
        <div class="pointer-events-none absolute -left-40 top-40 -z-10 size-[420px] rounded-full opacity-50"
             style="background: radial-gradient(circle, color-mix(in srgb, var(--brand) 14%, transparent), transparent 70%); filter: blur(40px);" />

        <!-- top bar -->
        <header class="mx-auto flex w-full max-w-[1100px] items-center justify-between">
            <Link href="/" class="flex items-center gap-2.5">
                <span class="grid size-8 place-items-center rounded-xl text-sm font-bold text-white shadow-e1" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                <span class="text-[15px] font-semibold tracking-[-0.01em] text-ink">{{ appName }}</span>
            </Link>
            <Link href="/" class="text-[13px] font-medium text-muted transition-colors hover:text-ink">← Back to site</Link>
        </header>

        <!-- centered card -->
        <main class="flex flex-1 items-center justify-center py-8">
            <div class="auth w-full max-w-[420px]">
                <div class="mb-7 text-center">
                    <h1 v-if="title" class="text-[26px] font-semibold tracking-[-0.025em] text-ink">{{ title }}</h1>
                    <p v-if="subtitle" class="mx-auto mt-2 max-w-[340px] text-[15px] leading-relaxed text-muted">{{ subtitle }}</p>
                </div>

                <div class="rounded-2xl border border-hairline bg-surface/90 p-7 shadow-e2 backdrop-blur-sm sm:p-8">
                    <slot />
                </div>

                <p class="mt-6 text-center text-xs text-faint">
                    Secured with 2FA · GDPR-ready · your data stays yours.
                </p>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Premium form controls, scoped to auth only (app forms keep their density). */
.auth :deep(input:not([type='checkbox'])) {
    height: 2.75rem;
    font-size: 15px;
    border-radius: 10px;
}
.auth :deep(label) {
    font-size: 13px;
    font-weight: 500;
    color: var(--color-ink-soft);
    margin-bottom: 0.4rem;
}
.auth :deep(button[type='submit']) {
    height: 2.75rem;
    font-size: 15px;
    border-radius: 10px;
    margin-top: 0.25rem;
}
</style>
