<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import PipelineDemo from '@/Components/marketing/PipelineDemo.vue';

defineProps({
    title: { type: String, default: null },
    subtitle: { type: String, default: null },
});

const appName = usePage().props.appName || 'Knit';
</script>

<template>
    <div class="grid min-h-[100dvh] bg-canvas lg:grid-cols-[1.05fr_1fr]">
        <!-- ============ LEFT — brand scene ============ -->
        <aside class="relative hidden flex-col justify-center overflow-hidden p-14 lg:flex"
               style="background: color-mix(in srgb, var(--brand) 6%, var(--color-canvas));">
            <!-- one soft halo + a faint grid (subtle, not cloudy) -->
            <div class="pointer-events-none absolute left-1/3 top-1/3 size-[360px] -translate-x-1/2 -translate-y-1/2 rounded-full opacity-40" style="background: radial-gradient(circle, var(--brand), transparent 60%); filter: blur(70px);" />
            <div class="pointer-events-none absolute inset-0 text-ink opacity-[0.04]" style="background-image: linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px); background-size: 60px 60px; mask-image: radial-gradient(ellipse 70% 60% at 35% 45%, #000, transparent 75%); -webkit-mask-image: radial-gradient(ellipse 70% 60% at 35% 45%, #000, transparent 75%);" />

            <Link href="/" class="absolute left-14 top-12 flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-xl text-base font-bold text-white shadow-e1" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                <span class="text-[17px] font-semibold tracking-[-0.01em] text-ink">{{ appName }}</span>
            </Link>

            <!-- centered content group -->
            <div class="relative w-full max-w-[420px]">
                <h2 class="text-[33px] font-semibold leading-[1.08] tracking-[-0.03em] text-ink">
                    Run sales, support<br />and projects in one place.
                </h2>
                <p class="mt-4 max-w-[380px] text-[15px] leading-relaxed text-muted">
                    {{ appName }} keeps every contact, deal and conversation connected — and your pipeline moving.
                </p>

                <div class="auth-float mt-9">
                    <PipelineDemo />
                </div>
            </div>
        </aside>

        <!-- ============ RIGHT — form ============ -->
        <div class="relative flex items-center justify-center border-hairline bg-surface px-6 py-10 sm:px-10 lg:border-l">
            <Link href="/" class="absolute right-8 top-8 text-[13px] font-medium text-muted transition-colors hover:text-ink">← Back to site</Link>

            <div class="auth w-full max-w-[372px]">
                <div class="mb-8 flex items-center gap-2.5 lg:hidden">
                    <span class="grid size-8 place-items-center rounded-xl text-sm font-bold text-white" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                    <span class="text-[15px] font-semibold tracking-[-0.01em] text-ink">{{ appName }}</span>
                </div>

                <div class="mb-7">
                    <h1 v-if="title" class="text-[26px] font-semibold tracking-[-0.025em] text-ink">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-2 text-[15px] leading-relaxed text-muted">{{ subtitle }}</p>
                </div>

                <slot />

                <p class="mt-8 text-center text-xs text-faint">Secured with 2FA · GDPR-ready · your data stays yours.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Premium form controls, scoped to auth only. */
.auth :deep(input:not([type='checkbox'])) { height: 2.75rem; font-size: 15px; border-radius: 10px; }
.auth :deep(label) { font-size: 13px; font-weight: 500; color: var(--color-ink-soft); margin-bottom: 0.4rem; }
.auth :deep(button[type='submit']) { height: 2.75rem; font-size: 15px; border-radius: 10px; margin-top: 0.25rem; }

@keyframes auth-float-kf { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
.auth-float { animation: auth-float-kf 7s ease-in-out infinite; }
@keyframes auth-toast-kf { 0%,100% { transform: translateY(0); } 50% { transform: translateY(6px); } }
.auth-toast { animation: auth-toast-kf 7s ease-in-out infinite; }

@media (prefers-reduced-motion: reduce) {
    .auth-float, .auth-toast { animation: none; }
}
</style>
