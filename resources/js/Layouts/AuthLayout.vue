<script setup>
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: null },
    subtitle: { type: String, default: null },
});

const appName = usePage().props.appName || 'Knit';
const features = ['Pipeline, inbox & automation in one place', 'Real-time, multi-tenant & AI-assisted', 'Secure by default — 2FA, RBAC, GDPR'];
</script>

<template>
    <div class="grid min-h-[100dvh] lg:grid-cols-[1.08fr_1fr]">
        <!-- ============ LEFT — dark brand scene ============ -->
        <div class="relative hidden flex-col justify-between overflow-hidden p-12 text-white lg:flex" style="background:#0a0a0f;">
            <!-- glows -->
            <div class="pointer-events-none absolute -left-32 -top-32 size-[520px] rounded-full opacity-50" style="background: radial-gradient(circle, var(--brand), transparent 62%); filter: blur(30px);" />
            <div class="pointer-events-none absolute -bottom-40 -right-24 size-[460px] rounded-full opacity-30" style="background: radial-gradient(circle, #8b5cf6, transparent 65%); filter: blur(40px);" />

            <Link href="/" class="relative flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-xl text-base font-bold text-white ring-1 ring-white/20" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                <span class="text-[17px] font-semibold tracking-[-0.01em]">{{ appName }}</span>
            </Link>

            <div class="relative">
                <h2 class="max-w-md text-[30px] font-semibold leading-[1.12] tracking-[-0.025em]">
                    The CRM your whole team <span class="text-[color:var(--brand)] [text-shadow:0_0_24px_color-mix(in_srgb,var(--brand)_60%,transparent)]">actually enjoys</span> using.
                </h2>

                <!-- floating glass product card -->
                <div class="auth-float relative mt-9 max-w-[360px]">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4 shadow-[0_30px_70px_-20px_rgba(0,0,0,0.8)] backdrop-blur-xl">
                        <div class="flex items-center justify-between">
                            <p class="text-[13px] font-semibold">Sales Pipeline</p>
                            <span class="text-[11px] text-white/50">Open · $147K</span>
                        </div>
                        <div class="mt-3 space-y-2">
                            <div v-for="(d, i) in [['Northwind Traders','$42K','won'],['Lumen Labs','$61K','open'],['Brightwave','$27K','open']]" :key="i"
                                 class="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.04] px-3 py-2">
                                <div class="flex items-center gap-2.5">
                                    <span class="size-1.5 rounded-full" :class="d[2]==='won' ? 'bg-positive' : 'bg-white/40'" />
                                    <span class="text-[12px] font-medium text-white/90">{{ d[0] }}</span>
                                </div>
                                <span class="text-[12px] font-semibold" :class="d[2]==='won' ? 'text-positive' : 'text-white/70'">{{ d[1] }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- floating toast -->
                    <div class="auth-toast absolute -bottom-5 -right-4 flex items-center gap-2 rounded-xl border border-white/10 bg-[#16161d] px-3 py-2 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.7)]">
                        <span class="grid size-6 place-items-center rounded-full bg-positive/20 text-positive">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5" /></svg>
                        </span>
                        <div>
                            <p class="text-[11px] font-semibold leading-tight">Deal won</p>
                            <p class="text-[10px] leading-tight text-white/50">Northwind · $42,000</p>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="relative space-y-2.5">
                <li v-for="f in features" :key="f" class="flex items-center gap-2.5 text-[13px] text-white/70">
                    <svg viewBox="0 0 24 24" fill="none" class="size-4 shrink-0 text-[color:var(--brand)]" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5" /></svg>
                    {{ f }}
                </li>
            </ul>
        </div>

        <!-- ============ RIGHT — form ============ -->
        <div class="flex items-center justify-center bg-canvas px-6 py-10 sm:px-10">
            <div class="auth w-full max-w-[380px]">
                <!-- mobile brand + back -->
                <div class="mb-8 flex items-center justify-between lg:hidden">
                    <Link href="/" class="flex items-center gap-2.5">
                        <span class="grid size-8 place-items-center rounded-xl text-sm font-bold text-white" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                        <span class="text-[15px] font-semibold tracking-[-0.01em] text-ink">{{ appName }}</span>
                    </Link>
                </div>
                <Link href="/" class="mb-6 hidden text-[13px] font-medium text-muted transition-colors hover:text-ink lg:inline-block">← Back to site</Link>

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

@keyframes auth-float-kf { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-9px); } }
.auth-float { animation: auth-float-kf 7s ease-in-out infinite; }
@keyframes auth-toast-kf { 0%,100% { transform: translateY(0); } 50% { transform: translateY(7px); } }
.auth-toast { animation: auth-toast-kf 7s ease-in-out infinite; }

@media (prefers-reduced-motion: reduce) {
    .auth-float, .auth-toast { animation: none; }
}
</style>
