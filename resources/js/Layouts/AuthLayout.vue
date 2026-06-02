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
    <div class="grid min-h-[100dvh] bg-canvas lg:grid-cols-[1.08fr_1fr]">
        <!-- ============ LEFT — brand scene (matches the landing) ============ -->
        <div class="relative hidden flex-col justify-between overflow-hidden p-12 lg:flex">
            <!-- same aurora + grid texture as the landing hero -->
            <div class="aurora aurora-a" :style="{ background: 'var(--brand)' }" />
            <div class="aurora aurora-b" />
            <div class="pointer-events-none absolute inset-0 text-ink opacity-[0.05]" style="background-image: linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px); background-size: 56px 56px; mask-image: radial-gradient(ellipse 80% 70% at 30% 20%, #000, transparent 75%); -webkit-mask-image: radial-gradient(ellipse 80% 70% at 30% 20%, #000, transparent 75%);" />

            <Link href="/" class="relative flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-xl text-base font-bold text-white shadow-e1" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                <span class="text-[17px] font-semibold tracking-[-0.01em] text-ink">{{ appName }}</span>
            </Link>

            <div class="relative">
                <h2 class="max-w-md text-[30px] font-semibold leading-[1.12] tracking-[-0.025em] text-ink">
                    The CRM your whole team <span class="text-[var(--brand)]">actually enjoys</span> using.
                </h2>

                <!-- light product card (same style as the landing surface cards) -->
                <div class="auth-float relative mt-9 max-w-[360px]">
                    <div class="rounded-2xl border border-hairline bg-surface p-4 shadow-e2">
                        <div class="flex items-center justify-between">
                            <p class="text-[13px] font-semibold tracking-[-0.01em] text-ink">Sales Pipeline</p>
                            <span class="text-[11px] text-muted">Open · <span class="nums font-medium text-ink-soft">$147K</span></span>
                        </div>
                        <div class="mt-3 space-y-1.5">
                            <div v-for="(d, i) in [['Northwind Traders','$42K','won'],['Lumen Labs','$61K','open'],['Brightwave','$27K','open']]" :key="i"
                                 class="flex items-center justify-between rounded-xl border border-hairline-soft bg-canvas/60 px-3 py-2">
                                <div class="flex items-center gap-2.5">
                                    <span class="size-1.5 rounded-full" :class="d[2]==='won' ? 'bg-positive' : 'bg-faint'" />
                                    <span class="text-[12px] font-medium text-ink">{{ d[0] }}</span>
                                </div>
                                <span class="nums text-[12px] font-semibold" :class="d[2]==='won' ? 'text-positive' : 'text-muted'">{{ d[1] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="auth-toast absolute -bottom-5 -right-4 flex items-center gap-2 rounded-xl border border-hairline bg-surface px-3 py-2 shadow-e2">
                        <span class="grid size-6 place-items-center rounded-full bg-positive/15 text-positive">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5" /></svg>
                        </span>
                        <div>
                            <p class="text-[11px] font-semibold leading-tight text-ink">Deal won</p>
                            <p class="text-[10px] leading-tight text-muted">Northwind · $42,000</p>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="relative space-y-2.5">
                <li v-for="f in features" :key="f" class="flex items-center gap-2.5 text-[13px] text-muted">
                    <svg viewBox="0 0 24 24" fill="none" class="size-4 shrink-0 text-[var(--brand)]" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5" /></svg>
                    {{ f }}
                </li>
            </ul>
        </div>

        <!-- ============ RIGHT — form ============ -->
        <div class="flex items-center justify-center border-hairline bg-surface px-6 py-10 sm:px-10 lg:border-l">
            <div class="auth w-full max-w-[380px]">
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

/* Aurora — identical to the landing hero for a cohesive look. */
.aurora { position: absolute; border-radius: 9999px; filter: blur(90px); }
.aurora-a { top: -120px; left: -60px; width: 460px; height: 460px; opacity: 0.18; animation: drift-a 20s ease-in-out infinite; }
.aurora-b { bottom: -120px; right: -80px; width: 380px; height: 380px; background: #8b5cf6; opacity: 0.14; animation: drift-b 24s ease-in-out infinite; }
@keyframes drift-a { 0%,100% { transform: translate(0,0); } 50% { transform: translate(40px, 30px); } }
@keyframes drift-b { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-50px, -20px); } }

@keyframes auth-float-kf { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-9px); } }
.auth-float { animation: auth-float-kf 7s ease-in-out infinite; }
@keyframes auth-toast-kf { 0%,100% { transform: translateY(0); } 50% { transform: translateY(7px); } }
.auth-toast { animation: auth-toast-kf 7s ease-in-out infinite; }

@media (prefers-reduced-motion: reduce) {
    .aurora, .auth-float, .auth-toast { animation: none; }
}
</style>
