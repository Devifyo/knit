<script setup>
import { usePage } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: null },
    subtitle: { type: String, default: null },
});

const appName = usePage().props.appName || 'Knit';
const features = [
    'Contacts, deals & pipeline in one fast workspace',
    'Shared inbox, automation & analytics built in',
    'Multi-tenant, real-time, and AI-assisted',
];
</script>

<template>
    <div class="grid min-h-[100dvh] lg:grid-cols-[1.05fr_1fr]">
        <!-- Brand panel (desktop) -->
        <div class="relative hidden flex-col justify-between overflow-hidden p-12 text-white lg:flex"
             style="background: linear-gradient(145deg, var(--brand), color-mix(in srgb, var(--brand) 62%, #09090b));">
            <!-- soft decorative glow + faint grid lines -->
            <div class="pointer-events-none absolute inset-0 opacity-[0.13]"
                 style="background-image: linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px); background-size: 54px 54px; mask-image: radial-gradient(ellipse 100% 80% at 30% 0%, #000, transparent 80%); -webkit-mask-image: radial-gradient(ellipse 100% 80% at 30% 0%, #000, transparent 80%);" />
            <div class="pointer-events-none absolute -right-24 -top-24 size-[420px] rounded-full"
                 style="background: radial-gradient(circle, rgba(255,255,255,0.22), transparent 70%);" />

            <div class="relative flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-xl bg-white/15 text-base font-bold ring-1 ring-white/25 backdrop-blur">{{ appName[0] }}</span>
                <span class="text-[17px] font-semibold tracking-[-0.01em]">{{ appName }}</span>
            </div>

            <div class="relative max-w-md">
                <h2 class="text-[28px] font-semibold leading-tight tracking-[-0.02em]">
                    The CRM your whole team actually enjoys using.
                </h2>
                <ul class="mt-7 space-y-3">
                    <li v-for="f in features" :key="f" class="flex items-start gap-2.5 text-[14px] text-white/85">
                        <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 size-4 shrink-0" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5" /></svg>
                        {{ f }}
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-white/60">© {{ appName }} — modern CRM for growing teams.</p>
        </div>

        <!-- Form panel -->
        <div class="flex items-center justify-center bg-canvas px-5 py-10 sm:px-8">
            <div class="w-full max-w-[380px]">
                <!-- compact brand mark (mobile) -->
                <div class="mb-7 flex items-center gap-2.5 lg:hidden">
                    <span class="grid size-9 place-items-center rounded-xl text-base font-bold text-white" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                    <span class="text-[16px] font-semibold tracking-[-0.01em] text-ink">{{ appName }}</span>
                </div>

                <div class="mb-6">
                    <h1 v-if="title" class="text-[22px] font-semibold tracking-[-0.02em] text-ink">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-1.5 text-sm text-muted">{{ subtitle }}</p>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
