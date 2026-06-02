<script setup>
import { ref, onMounted, onUnmounted, h } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineOptions({ layout: null });

const appName = usePage().props.appName || 'Knit';

// Sticky nav background on scroll.
const scrolled = ref(false);
const onScroll = () => { scrolled.value = window.scrollY > 8; };
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

// Reveal-on-scroll directive (no dependency; respects reduced-motion via CSS).
const vReveal = {
    mounted(el, binding) {
        el.classList.add('reveal');
        if (binding.value) el.style.transitionDelay = `${binding.value}ms`;
        const io = new IntersectionObserver(([e]) => {
            if (e.isIntersecting) { el.classList.add('is-visible'); io.disconnect(); }
        }, { threshold: 0.12 });
        io.observe(el);
    },
};

const icon = (d) => () => h('svg', { viewBox: '0 0 24 24', fill: 'none', class: 'size-5', stroke: 'currentColor', 'stroke-width': '1.7', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [h('path', { d })]);
const features = [
    { t: 'Pipeline & deals', d: 'Drag-and-drop kanban that syncs live across your team, with quotes and CPQ built in.', i: icon('M3 7h18M3 7l2 13h14l2-13M9 11v5M15 11v5') },
    { t: 'Shared inbox', d: 'Email, threading, internal notes and @mentions — every message tied to the right contact.', i: icon('M22 12h-6l-2 3h-4l-2-3H2M5.45 5.11L2 12v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3.45-6.89A2 2 0 0016.76 4H7.24a2 2 0 00-1.79 1.11z') },
    { t: 'Automation', d: 'Visual workflows: triggers, waits, conditions and actions that run on a reliable queue.', i: icon('M6 3v12M6 21a3 3 0 100-6 3 3 0 000 6zM18 9a3 3 0 100-6 3 3 0 000 6zm0 0a9 9 0 01-9 9') },
    { t: 'AI assist', d: 'Lead scoring, meeting summaries and reply drafts — gracefully optional, per workspace.', i: icon('M12 3l1.9 5.8L20 10l-6.1 1.2L12 17l-1.9-5.8L4 10l6.1-1.2z') },
    { t: 'Analytics', d: 'Live dashboards and filterable reports you can export to CSV, Excel or PDF.', i: icon('M3 3v18h18M7 13l3-3 3 3 5-6') },
    { t: 'Secure & compliant', d: 'Per-workspace 2FA, IP allow-listing, audit trails and GDPR export/erase.', i: icon('M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4zM9 12l2 2 4-4') },
];
const modules = ['Real Estate', 'Recruitment', 'Education', 'Healthcare'];
</script>

<template>
    <Head title="Knit — the modern CRM your team enjoys" />

    <div class="min-h-[100dvh] bg-canvas text-ink">
        <!-- Nav -->
        <header :class="['fixed inset-x-0 top-0 z-50 transition-all duration-300', scrolled ? 'border-b border-hairline bg-surface/80 backdrop-blur' : 'border-b border-transparent']">
            <div class="mx-auto flex h-16 max-w-[1200px] items-center justify-between px-5 lg:px-8">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-8 place-items-center rounded-xl text-sm font-bold text-white shadow-e1" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                    <span class="text-[16px] font-semibold tracking-[-0.01em]">{{ appName }}</span>
                </div>
                <nav class="hidden items-center gap-7 text-[13px] font-medium text-muted md:flex">
                    <a href="#features" class="transition-colors hover:text-ink">Features</a>
                    <a href="#modules" class="transition-colors hover:text-ink">Modules</a>
                    <a href="#start" class="transition-colors hover:text-ink">Get started</a>
                </nav>
                <div class="flex items-center gap-2">
                    <Link href="/login" class="rounded-[var(--radius-control)] px-3 py-1.5 text-[13px] font-medium text-ink-soft transition-colors hover:bg-sunken">Sign in</Link>
                    <Link href="/register" class="rounded-[var(--radius-control)] px-3.5 py-1.5 text-[13px] font-semibold text-white shadow-e1 transition-transform active:translate-y-px" :style="{ background: 'var(--brand)' }">Get started</Link>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden px-5 pb-20 pt-32 lg:px-8 lg:pt-40">
            <!-- aurora -->
            <div class="pointer-events-none absolute inset-0 -z-10">
                <div class="aurora aurora-a" :style="{ background: 'var(--brand)' }" />
                <div class="aurora aurora-b" />
                <div class="absolute inset-0 opacity-[0.05] dark:opacity-[0.08]" style="background-image: radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0); background-size: 26px 26px;" />
            </div>

            <div class="mx-auto max-w-[1200px]">
                <div class="grid items-center gap-12 lg:grid-cols-[1.05fr_1fr]">
                    <div>
                        <span class="rise inline-flex items-center gap-2 rounded-full border border-hairline bg-surface/70 px-3 py-1 text-xs font-medium text-muted backdrop-blur" style="animation-delay:.05s">
                            <span class="size-1.5 rounded-full" :style="{ background: 'var(--brand)' }" /> Multi-tenant CRM · real-time · AI-assisted
                        </span>
                        <h1 class="rise mt-5 text-[clamp(2.2rem,5vw,3.6rem)] font-semibold leading-[1.05] tracking-[-0.03em]" style="animation-delay:.12s">
                            The CRM your whole team<br class="hidden sm:block" />
                            <span class="text-[var(--brand)]">actually enjoys</span> using.
                        </h1>
                        <p class="rise mt-5 max-w-xl text-[17px] leading-relaxed text-muted" style="animation-delay:.2s">
                            Contacts, deals, a shared inbox, automation and analytics — in one fast, modern workspace. Built for teams that move quickly.
                        </p>
                        <div class="rise mt-8 flex flex-wrap items-center gap-3" style="animation-delay:.28s">
                            <Link href="/register" class="group inline-flex items-center gap-2 rounded-[var(--radius-control)] px-5 py-2.5 text-sm font-semibold text-white shadow-e2 transition-transform hover:-translate-y-0.5 active:translate-y-0" :style="{ background: 'var(--brand)' }">
                                Start free
                                <svg viewBox="0 0 24 24" fill="none" class="size-4 transition-transform group-hover:translate-x-0.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                            </Link>
                            <Link href="/login" class="inline-flex items-center rounded-[var(--radius-control)] border border-hairline bg-surface px-5 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-sunken">Sign in</Link>
                        </div>
                        <p class="rise mt-4 text-xs text-faint" style="animation-delay:.34s">No credit card required · set up in under a minute</p>
                    </div>

                    <!-- App preview mock -->
                    <div class="rise float relative" style="animation-delay:.3s">
                        <div class="rounded-2xl border border-hairline bg-surface/90 p-3 shadow-e3 backdrop-blur">
                            <div class="mb-3 flex items-center gap-1.5 px-1">
                                <span class="size-2.5 rounded-full bg-critical/60" /><span class="size-2.5 rounded-full bg-warning/60" /><span class="size-2.5 rounded-full bg-positive/60" />
                                <span class="ml-3 text-[11px] text-faint">{{ appName.toLowerCase() }}.app/deals</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2.5">
                                <div v-for="(col, ci) in [['New', 2], ['Qualified', 2], ['Won', 1]]" :key="ci" class="rounded-xl bg-sunken/70 p-2">
                                    <div class="mb-2 flex items-center justify-between px-1 text-[10px] font-semibold uppercase tracking-wide text-faint">
                                        {{ col[0] }} <span class="rounded-full bg-surface px-1.5 ring-1 ring-hairline">{{ col[1] }}</span>
                                    </div>
                                    <div v-for="n in col[1]" :key="n" class="pop mb-2 rounded-lg border border-hairline bg-surface p-2.5 shadow-e1" :style="{ animationDelay: (0.5 + ci*0.12 + n*0.08) + 's' }">
                                        <div class="h-1.5 w-3/4 rounded-full bg-ink/15" />
                                        <div class="mt-2 flex items-center justify-between">
                                            <div class="h-1.5 w-10 rounded-full" :style="{ background: 'color-mix(in srgb, var(--brand) 55%, transparent)' }" />
                                            <div class="size-4 rounded-full bg-ink/10" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- floating stat chip -->
                        <div class="pop absolute -bottom-4 -left-4 hidden rounded-xl border border-hairline bg-surface px-3 py-2 shadow-e2 sm:block" style="animation-delay:1s">
                            <p class="text-[10px] font-medium text-muted">Pipeline value</p>
                            <p class="nums text-base font-semibold tracking-[-0.01em]">$1.2M <span class="text-xs font-medium text-positive">▲</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="px-5 py-20 lg:px-8">
            <div class="mx-auto max-w-[1200px]">
                <div v-reveal class="mx-auto max-w-2xl text-center">
                    <h2 class="text-[clamp(1.7rem,3.5vw,2.4rem)] font-semibold tracking-[-0.02em]">Everything a sales team needs</h2>
                    <p class="mt-3 text-muted">One connected workspace instead of five disconnected tools.</p>
                </div>
                <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(f, i) in features" :key="f.t" v-reveal="i * 70"
                         class="group rounded-2xl border border-hairline bg-surface p-6 shadow-e1 transition-all hover:-translate-y-1 hover:shadow-e2">
                        <div class="grid size-11 place-items-center rounded-xl brand-wash text-[var(--brand)] transition-transform group-hover:scale-105">
                            <component :is="f.i" />
                        </div>
                        <h3 class="mt-4 text-[15px] font-semibold tracking-[-0.01em]">{{ f.t }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ f.d }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modules -->
        <section id="modules" class="px-5 py-16 lg:px-8">
            <div v-reveal class="mx-auto max-w-[1200px] overflow-hidden rounded-3xl border border-hairline bg-surface p-10 text-center shadow-e1">
                <h2 class="text-[clamp(1.5rem,3vw,2rem)] font-semibold tracking-[-0.02em]">Installable industry modules</h2>
                <p class="mx-auto mt-3 max-w-xl text-muted">Turn on the vertical you need — each adds its own records, fully tenant-scoped.</p>
                <div class="mt-7 flex flex-wrap justify-center gap-3">
                    <span v-for="(m, i) in modules" :key="m" v-reveal="i * 80"
                          class="rounded-full border border-hairline bg-sunken px-4 py-2 text-sm font-medium text-ink-soft transition-colors hover:border-[var(--brand)] hover:text-[var(--brand)]">{{ m }}</span>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section id="start" class="px-5 py-20 lg:px-8">
            <div v-reveal class="relative mx-auto max-w-[1100px] overflow-hidden rounded-3xl p-12 text-center text-white shadow-e3"
                 :style="{ background: 'linear-gradient(135deg, var(--brand), color-mix(in srgb, var(--brand) 60%, #09090b))' }">
                <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 24px 24px;" />
                <h2 class="relative text-[clamp(1.8rem,4vw,2.6rem)] font-semibold tracking-[-0.02em]">Ready to knit your sales together?</h2>
                <p class="relative mx-auto mt-3 max-w-lg text-white/80">Create your workspace in under a minute. Bring your team along.</p>
                <Link href="/register" class="relative mt-8 inline-flex items-center gap-2 rounded-[var(--radius-control)] bg-white px-6 py-3 text-sm font-semibold text-ink shadow-e2 transition-transform hover:-translate-y-0.5">
                    Get started free
                    <svg viewBox="0 0 24 24" fill="none" class="size-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-hairline px-5 py-10 lg:px-8">
            <div class="mx-auto flex max-w-[1200px] flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-7 place-items-center rounded-lg text-xs font-bold text-white" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                    <span class="text-sm font-medium text-muted">© {{ appName }} — modern CRM for growing teams.</span>
                </div>
                <div class="flex gap-6 text-sm text-muted">
                    <Link href="/login" class="hover:text-ink">Sign in</Link>
                    <Link href="/register" class="hover:text-ink">Create workspace</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* On-load rise */
@keyframes rise-kf { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
.rise { opacity: 0; animation: rise-kf 0.7s cubic-bezier(0.2, 0.7, 0.3, 1) forwards; }

/* Card pop-in */
@keyframes pop-kf { from { opacity: 0; transform: translateY(10px) scale(0.98); } to { opacity: 1; transform: none; } }
.pop { opacity: 0; animation: pop-kf 0.55s cubic-bezier(0.2, 0.7, 0.3, 1) forwards; }

/* Scroll reveal */
.reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.7s cubic-bezier(0.2,0.7,0.3,1), transform 0.7s cubic-bezier(0.2,0.7,0.3,1); }
.reveal.is-visible { opacity: 1; transform: none; }

/* Gentle float on the hero visual */
@keyframes float-kf { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.float { animation: float-kf 6s ease-in-out infinite; animation-delay: 1.2s; }

/* Aurora blobs */
.aurora { position: absolute; border-radius: 9999px; filter: blur(80px); opacity: 0.18; }
.aurora-a { top: -120px; right: -60px; width: 460px; height: 460px; animation: drift-a 18s ease-in-out infinite; }
.aurora-b { top: 40px; left: -120px; width: 380px; height: 380px; background: #8b5cf6; opacity: 0.12; animation: drift-b 22s ease-in-out infinite; }
@keyframes drift-a { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-40px, 30px); } }
@keyframes drift-b { 0%,100% { transform: translate(0,0); } 50% { transform: translate(50px, -20px); } }

@media (prefers-reduced-motion: reduce) {
    .rise, .pop, .float, .aurora { animation: none; opacity: 1; }
    .rise, .pop { transform: none; }
    .reveal { opacity: 1; transform: none; transition: none; }
}
</style>
