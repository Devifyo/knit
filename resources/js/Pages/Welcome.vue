<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineOptions({ layout: null });

const appName = usePage().props.appName || 'Knit';

const scrolled = ref(false);
const onScroll = () => { scrolled.value = window.scrollY > 8; };
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

const vReveal = {
    mounted(el, binding) {
        el.classList.add('reveal');
        if (binding.value) el.style.transitionDelay = `${binding.value}ms`;
        const io = new IntersectionObserver(([e]) => {
            if (e.isIntersecting) { el.classList.add('is-visible'); io.disconnect(); }
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        io.observe(el);
    },
};

/* ---- Live pipeline animation (hero) ---- */
const stages = ['New', 'Qualified', 'Won'];
const deals = ref([
    { id: 1, co: 'Northwind Traders', amt: 42000, who: 'SC', stage: 0 },
    { id: 2, co: 'Vertex Industries', amt: 18500, who: 'MD', stage: 0 },
    { id: 3, co: 'Lumen Labs', amt: 61000, who: 'AO', stage: 1 },
    { id: 4, co: 'Brightwave', amt: 27300, who: 'JL', stage: 1 },
    { id: 5, co: 'Cedar & Co.', amt: 9800, who: 'PR', stage: 2 },
    { id: 6, co: 'Halcyon Group', amt: 34500, who: 'Tn', stage: 0 },
]);
const inStage = (s) => deals.value.filter((d) => d.stage === s);
const fmt = (n) => '$' + (n / 1000).toFixed(n % 1000 === 0 ? 0 : 1) + 'K';
const openValue = computed(() => deals.value.filter((d) => d.stage < 2).reduce((a, d) => a + d.amt, 0));

let ptr = 2, timer = null;
const advance = () => {
    // move one card forward through the funnel; recycle Won → New so it loops.
    const d = deals.value[ptr % deals.value.length];
    d.stage = (d.stage + 1) % 3;
    ptr++;
};
onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    timer = setInterval(advance, 2100);
});
onUnmounted(() => timer && clearInterval(timer));

const navMock = [
    { label: 'Dashboard', d: 'M4 13h6V4H4v9zm10 7h6v-9h-6v9z' },
    { label: 'Contacts', d: 'M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 21a6 6 0 0112 0' },
    { label: 'Deals', d: 'M3 7h18l-2 13H5L3 7z', active: true },
    { label: 'Inbox', d: 'M22 12h-6l-2 3h-4l-2-3H2' },
    { label: 'Analytics', d: 'M3 3v18h18M7 13l3-3 3 3 5-6' },
];

const inbox = [
    { who: 'Priya Raman', sub: 'Re: Q3 renewal — pricing', t: '2m', unread: true },
    { who: 'David Okafor', sub: 'Demo follow-up + next steps', t: '24m' },
    { who: 'Lena Fischer', sub: 'Contract redlines attached', t: '1h' },
];

const groups = [
    { name: 'CRM core', items: ['Companies & contacts', 'Activity timeline & notes', 'Leads — capture, score, convert', 'Deals pipeline (live drag-drop)', 'Accounts & renewals', 'Custom fields & tags'] },
    { name: 'Sales & CPQ', items: ['Multiple pipelines', 'Product catalog', 'Quotes & line items', 'Per-line discounts', 'Multi-currency', 'Branded quote PDFs'] },
    { name: 'Automation', items: ['Visual workflow builder', 'Event triggers', 'Wait / delay steps', 'Conditions & branching', 'Tasks & reminders', 'Reliable queue execution'] },
    { name: 'Communication', items: ['Shared team inbox', 'Email threading', 'Internal notes & @mentions', 'Team chat with presence', 'Real-time everywhere', 'Inbound email webhook'] },
    { name: 'Support', items: ['Tickets & replies', 'SLA timers', 'Auto-assignment', 'Escalation rules', 'Knowledge base', 'Self-service portal + AI'] },
    { name: 'Marketing', items: ['Email campaigns', 'A/B subject testing', 'Open & click tracking', 'Landing forms', 'Lead nurture sequences', 'SMS / WhatsApp adapter'] },
    { name: 'AI assist', items: ['Lead scoring', 'Meeting summaries → tasks', 'Ticket reply assist', 'Deal insights', 'Per-workspace toggle', 'Graceful fallback'] },
    { name: 'Analytics', items: ['Live dashboards', 'Pipeline by stage', 'Team leaderboard', 'Filterable reports', 'CSV · Excel · PDF export'] },
    { name: 'Collaboration', items: ['Projects', 'Kanban tasks & subtasks', 'Time tracking', 'File sharing', 'Workspace activity feed'] },
    { name: 'Security & compliance', items: ['Roles & granular permissions', '2FA enforcement', 'IP allow-listing', 'Audit trail', 'GDPR export & erasure', 'Login history'] },
    { name: 'Billing & platform', items: ['Plans & subscriptions', 'Free trials & coupons', 'Invoices + PDF', 'Plan-based gating', 'Signed outbound webhooks', 'Multi-tenant by design'] },
    { name: 'Industry modules', items: ['Real Estate', 'Recruitment', 'Education', 'Healthcare', 'Installable per workspace'] },
];
const groupIcon = (i) => ['M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 21a6 6 0 0112 0', 'M3 7h18l-2 13H5L3 7z', 'M6 3v12M6 21a3 3 0 100-6 3 3 0 000 6zM18 9a3 3 0 100-6 3 3 0 000 6zm0 0a9 9 0 01-9 9', 'M22 12h-6l-2 3h-4l-2-3H2M5 5l-3 7v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3-7z', 'M3 9a2 2 0 012-2h14a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 000-4V9z', 'M3 8l8 5 8-5M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z', 'M12 3l1.9 5.8L20 10l-6.1 1.2L12 17l-1.9-5.8L4 10l6.1-1.2z', 'M3 3v18h18M7 13l3-3 3 3 5-6', 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z', 'M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4zM9 12l2 2 4-4', 'M2 7h20v12a2 2 0 01-2 2H4a2 2 0 01-2-2V7zM2 7l2-3h16l2 3', 'M12 2l9 5-9 5-9-5 9-5zM3 12l9 5 9-5'][i];
</script>

<template>
    <Head title="Knit — the all-in-one CRM your team enjoys" />

    <div class="min-h-[100dvh] bg-canvas text-ink">
        <!-- NAV -->
        <header :class="['fixed inset-x-0 top-0 z-50 transition-all duration-300', scrolled ? 'border-b border-hairline bg-surface/75 backdrop-blur-xl' : 'border-b border-transparent']">
            <div class="mx-auto flex h-16 max-w-[1180px] items-center justify-between px-5 lg:px-8">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-8 place-items-center rounded-xl text-sm font-bold text-white shadow-e1" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                    <span class="text-[16px] font-semibold tracking-[-0.01em]">{{ appName }}</span>
                </div>
                <nav class="hidden items-center gap-8 text-[13px] font-medium text-muted md:flex">
                    <a href="#product" class="transition-colors hover:text-ink">Product</a>
                    <a href="#capabilities" class="transition-colors hover:text-ink">Capabilities</a>
                    <a href="#modules" class="transition-colors hover:text-ink">Modules</a>
                </nav>
                <div class="flex items-center gap-2">
                    <Link href="/login" class="rounded-[var(--radius-control)] px-3 py-1.5 text-[13px] font-medium text-ink-soft transition-colors hover:bg-sunken">Sign in</Link>
                    <Link href="/register" class="rounded-[var(--radius-control)] px-3.5 py-1.5 text-[13px] font-semibold text-white shadow-e1 transition-transform active:translate-y-px" :style="{ background: 'var(--brand)' }">Get started</Link>
                </div>
            </div>
        </header>

        <!-- HERO -->
        <section class="relative overflow-hidden px-5 pb-8 pt-32 lg:px-8 lg:pt-40">
            <div class="pointer-events-none absolute inset-0 -z-10">
                <div class="aurora aurora-a" :style="{ background: 'var(--brand)' }" />
                <div class="aurora aurora-b" />
                <div class="absolute inset-0 opacity-[0.04] dark:opacity-[0.07]" style="background-image: radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0); background-size: 28px 28px;" />
            </div>

            <div class="mx-auto max-w-[760px] text-center">
                <span class="rise inline-flex items-center gap-2 rounded-full border border-hairline bg-surface/70 px-3 py-1 text-xs font-medium text-muted backdrop-blur" style="animation-delay:.04s">
                    <span class="relative flex size-1.5"><span class="absolute inline-flex size-full animate-ping rounded-full opacity-60" :style="{ background: 'var(--brand)' }" /><span class="relative inline-flex size-1.5 rounded-full" :style="{ background: 'var(--brand)' }" /></span>
                    One workspace · 12 connected modules
                </span>
                <h1 class="rise mt-6 text-[clamp(2.4rem,6vw,4rem)] font-semibold leading-[1.04] tracking-[-0.035em]" style="animation-delay:.1s">
                    The all-in-one CRM your<br class="hidden sm:block" />
                    team <span class="text-[var(--brand)]">actually enjoys</span> using.
                </h1>
                <p class="rise mx-auto mt-6 max-w-xl text-[17px] leading-relaxed text-muted" style="animation-delay:.18s">
                    Pipeline, inbox, support, marketing, automation, analytics and AI — connected in one fast, multi-tenant workspace. Replace five tools with one.
                </p>
                <div class="rise mt-8 flex flex-wrap items-center justify-center gap-3" style="animation-delay:.26s">
                    <Link href="/register" class="group inline-flex items-center gap-2 rounded-[var(--radius-control)] px-5 py-2.5 text-sm font-semibold text-white shadow-e2 transition-transform hover:-translate-y-0.5" :style="{ background: 'var(--brand)' }">
                        Start free
                        <svg viewBox="0 0 24 24" fill="none" class="size-4 transition-transform group-hover:translate-x-0.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </Link>
                    <Link href="/login" class="inline-flex items-center rounded-[var(--radius-control)] border border-hairline bg-surface px-5 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-sunken">Sign in</Link>
                </div>
            </div>

            <!-- Product window with a LIVE pipeline -->
            <div id="product" class="rise mx-auto mt-14 max-w-[1040px]" style="animation-delay:.32s">
              <div class="float">
                <div class="overflow-hidden rounded-2xl border border-hairline bg-surface shadow-e3">
                    <div class="flex items-center gap-1.5 border-b border-hairline-soft px-4 py-2.5">
                        <span class="size-2.5 rounded-full bg-critical/50" /><span class="size-2.5 rounded-full bg-warning/50" /><span class="size-2.5 rounded-full bg-positive/50" />
                        <span class="mx-auto rounded-md bg-sunken px-3 py-0.5 text-[11px] text-faint">{{ appName.toLowerCase() }}.devifyo.cloud/deals</span>
                    </div>
                    <div class="grid grid-cols-[60px_1fr] sm:grid-cols-[200px_1fr]">
                        <!-- sidebar -->
                        <div class="border-r border-hairline-soft bg-surface p-2.5 sm:p-3">
                            <div class="mb-3 flex items-center gap-2 px-1">
                                <span class="grid size-6 place-items-center rounded-lg text-[10px] font-bold text-white" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                                <span class="hidden text-[13px] font-semibold sm:inline">{{ appName }}</span>
                            </div>
                            <div v-for="n in navMock" :key="n.label" :class="['mb-0.5 flex items-center gap-2.5 rounded-lg px-2 py-1.5 text-[12px] font-medium', n.active ? 'brand-wash text-[var(--brand)]' : 'text-ink-soft']">
                                <svg viewBox="0 0 24 24" fill="none" class="size-4 shrink-0" :class="n.active ? 'text-[var(--brand)]' : 'text-faint'" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path :d="n.d" /></svg>
                                <span class="hidden sm:inline">{{ n.label }}</span>
                            </div>
                        </div>
                        <!-- board -->
                        <div class="bg-canvas p-3 sm:p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <div>
                                    <p class="text-[13px] font-semibold tracking-[-0.01em]">Sales Pipeline</p>
                                    <p class="text-[11px] text-muted">Open value <span class="nums font-medium text-ink">{{ fmt(openValue) }}</span></p>
                                </div>
                                <span class="rounded-md px-2.5 py-1 text-[11px] font-semibold text-white" :style="{ background: 'var(--brand)' }">+ New deal</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 sm:gap-2.5">
                                <div v-for="(label, s) in stages" :key="label" class="rounded-xl bg-sunken/60 p-1.5 sm:p-2">
                                    <div class="mb-2 flex items-center justify-between px-1 text-[9px] font-semibold uppercase tracking-wide text-faint">
                                        {{ label }} <span class="rounded-full bg-surface px-1.5 ring-1 ring-hairline">{{ inStage(s).length }}</span>
                                    </div>
                                    <TransitionGroup tag="div" name="flow" class="relative space-y-1.5">
                                        <div v-for="d in inStage(s)" :key="d.id"
                                             :class="['deal rounded-lg border bg-surface p-2 shadow-e1', s === 2 ? 'border-positive/30' : 'border-hairline']">
                                            <p class="truncate text-[11px] font-semibold tracking-[-0.01em] text-ink">{{ d.co }}</p>
                                            <div class="mt-1.5 flex items-center justify-between">
                                                <span class="nums text-[11px] font-medium" :class="s === 2 ? 'text-positive' : 'text-muted'">{{ fmt(d.amt) }}</span>
                                                <span class="grid size-4 place-items-center rounded-full bg-sunken text-[8px] font-semibold text-ink-soft ring-1 ring-hairline">{{ d.who }}</span>
                                            </div>
                                        </div>
                                    </TransitionGroup>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              </div>
            </div>
        </section>

        <!-- BENTO -->
        <section class="px-5 pb-16 pt-10 lg:px-8">
            <div class="mx-auto max-w-[1180px]">
                <div v-reveal class="mb-10 max-w-2xl">
                    <p class="text-sm font-semibold text-[var(--brand)]">One connected system</p>
                    <h2 class="mt-2 text-[clamp(1.8rem,4vw,2.6rem)] font-semibold tracking-[-0.025em]">Not another single-purpose tool.</h2>
                    <p class="mt-3 text-muted">Every record links to the next — a contact's deals, a deal's quotes, a project on a won deal, a webhook on every change.</p>
                </div>

                <div class="grid auto-rows-[210px] grid-cols-1 gap-4 md:grid-cols-6">
                    <!-- Inbox (real) -->
                    <div v-reveal class="group flex flex-col overflow-hidden rounded-2xl border border-hairline bg-surface p-6 shadow-e1 transition-shadow hover:shadow-e2 md:col-span-3 md:row-span-2">
                        <h3 class="text-[15px] font-semibold tracking-[-0.01em]">Shared inbox</h3>
                        <p class="mt-1.5 text-sm text-muted">Threaded email, internal notes and @mentions — every message tied to a contact.</p>
                        <div class="mt-5 flex-1 space-y-2">
                            <div v-for="(m, i) in inbox" :key="i" class="flex items-center gap-3 rounded-xl border border-hairline-soft bg-canvas/60 p-2.5 transition-transform group-hover:translate-x-0.5" :style="{ transitionDelay: i*40 + 'ms' }">
                                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-sunken text-[11px] font-semibold text-ink-soft ring-1 ring-hairline">{{ m.who.split(' ').map(w=>w[0]).join('') }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate text-[13px] font-semibold text-ink">{{ m.who }}</p>
                                        <span v-if="m.unread" class="size-1.5 shrink-0 rounded-full" :style="{ background: 'var(--brand)' }" />
                                        <span class="ml-auto shrink-0 text-[11px] text-faint">{{ m.t }}</span>
                                    </div>
                                    <p class="truncate text-[12px] text-muted">{{ m.sub }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI -->
                    <div v-reveal="60" class="relative overflow-hidden rounded-2xl border border-hairline bg-surface p-6 shadow-e1 transition-shadow hover:shadow-e2 md:col-span-3">
                        <div class="absolute -right-6 -top-6 size-28 rounded-full opacity-20 blur-2xl" :style="{ background: 'var(--brand)' }" />
                        <div class="flex items-center justify-between">
                            <h3 class="text-[15px] font-semibold tracking-[-0.01em]">AI assist</h3>
                            <svg viewBox="0 0 24 24" fill="currentColor" class="size-4 text-[var(--brand)]"><path d="M12 2l1.9 5.8L20 9.7l-6.1 1.9L12 17l-1.9-5.4L4 9.7l6.1-1.9z" /></svg>
                        </div>
                        <p class="mt-1.5 text-sm text-muted">Lead scoring, meeting summaries and reply drafts.</p>
                        <div class="mt-4 flex items-center gap-3">
                            <div class="relative grid size-12 place-items-center">
                                <svg viewBox="0 0 36 36" class="size-12 -rotate-90"><circle cx="18" cy="18" r="15.5" fill="none" stroke="currentColor" class="text-hairline" stroke-width="3" /><circle cx="18" cy="18" r="15.5" fill="none" stroke="var(--brand)" stroke-width="3" stroke-linecap="round" stroke-dasharray="97.4" stroke-dashoffset="12.7" /></svg>
                                <span class="absolute nums text-[12px] font-bold">87</span>
                            </div>
                            <div>
                                <p class="text-[13px] font-semibold text-ink">Strong fit</p>
                                <p class="text-[12px] text-muted">Enterprise source · work email</p>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics (animated bars) -->
                    <div v-reveal="120" class="overflow-hidden rounded-2xl border border-hairline bg-surface p-6 shadow-e1 transition-shadow hover:shadow-e2 md:col-span-3">
                        <h3 class="text-[15px] font-semibold tracking-[-0.01em]">Analytics & reports</h3>
                        <p class="mt-1.5 text-sm text-muted">Live KPIs · export to CSV, Excel or PDF.</p>
                        <div class="chart mt-4 flex h-14 items-end gap-1.5">
                            <span v-for="(b, i) in [42, 64, 50, 78, 60, 92, 71]" :key="i" class="bar flex-1 rounded-t-md" :style="{ '--h': b + '%', transitionDelay: i*60 + 'ms', background: 'color-mix(in srgb, var(--brand) ' + (45 + b/2) + '%, transparent)' }" />
                        </div>
                    </div>

                    <!-- Automation -->
                    <div v-reveal class="overflow-hidden rounded-2xl border border-hairline bg-surface p-6 shadow-e1 transition-shadow hover:shadow-e2 md:col-span-4">
                        <h3 class="text-[15px] font-semibold tracking-[-0.01em]">Automation that runs itself</h3>
                        <p class="mt-1.5 text-sm text-muted">Triggers, waits, conditions and actions on a reliable queue.</p>
                        <div class="mt-4 flex flex-wrap items-center gap-1.5">
                            <template v-for="(s, i) in ['Lead created','Wait 1 day','If no reply','Send email','Create task']" :key="s">
                                <span class="rounded-lg border border-hairline bg-sunken px-2.5 py-1.5 text-[11px] font-medium text-ink-soft">{{ s }}</span>
                                <svg v-if="i < 4" viewBox="0 0 24 24" fill="none" class="size-3.5 text-faint" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </template>
                        </div>
                    </div>

                    <!-- Security -->
                    <div v-reveal="60" class="flex flex-col justify-between overflow-hidden rounded-2xl border border-hairline bg-surface p-6 shadow-e1 transition-shadow hover:shadow-e2 md:col-span-2">
                        <div>
                            <h3 class="text-[15px] font-semibold tracking-[-0.01em]">Secure by default</h3>
                            <p class="mt-1.5 text-sm text-muted">Tenant isolation, 2FA, audit trails.</p>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span v-for="b in ['2FA','RBAC','GDPR','Audit']" :key="b" class="rounded-full bg-sunken px-2.5 py-0.5 text-[11px] font-medium text-muted ring-1 ring-hairline">{{ b }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- DEEP DIVE -->
        <section class="px-5 py-12 lg:px-8">
            <div class="mx-auto max-w-[1180px] space-y-20">
                <div v-reveal class="grid items-center gap-10 lg:grid-cols-2">
                    <div>
                        <p class="text-sm font-semibold text-[var(--brand)]">Customer support</p>
                        <h3 class="mt-2 text-[clamp(1.5rem,3vw,2rem)] font-semibold tracking-[-0.02em]">Tickets, SLAs and a self-service portal</h3>
                        <p class="mt-3 text-muted">Channel-agnostic intake, first-response SLA timers, least-loaded auto-assignment, escalation, and a public help center with an AI chatbot answering from your knowledge base.</p>
                    </div>
                    <div class="rounded-2xl border border-hairline bg-surface p-5 shadow-e1">
                        <div v-for="(t, i) in [['Production is down','urgent','critical'],['How do I export contacts?','normal','info'],['Question about invoices','low','neutral']]" :key="i" class="flex items-center justify-between border-b border-hairline-soft py-3 last:border-0">
                            <div class="flex items-center gap-3">
                                <span class="size-2 rounded-full" :class="{critical:'bg-critical',info:'bg-info',neutral:'bg-faint'}[t[2]]" />
                                <span class="text-[13px] text-ink-soft">{{ t[0] }}</span>
                            </div>
                            <span class="rounded-full bg-sunken px-2 py-0.5 text-[10px] font-medium text-muted ring-1 ring-hairline">{{ t[1] }}</span>
                        </div>
                    </div>
                </div>

                <div v-reveal class="grid items-center gap-10 lg:grid-cols-2">
                    <div class="order-2 rounded-2xl border border-hairline bg-surface p-5 shadow-e1 lg:order-1">
                        <div class="grid grid-cols-3 gap-2">
                            <div v-for="(c, ci) in [['To do',['Audit flow','Pull metrics']],['Doing',['Draft checklist']],['Done',['Kickoff','Wireframes']]]" :key="ci" class="rounded-xl bg-sunken/60 p-2">
                                <div class="mb-2 px-1 text-[9px] font-semibold uppercase tracking-wide text-faint">{{ c[0] }}</div>
                                <div v-for="task in c[1]" :key="task" class="mb-1.5 rounded-lg border border-hairline bg-surface p-2 shadow-e1">
                                    <p class="truncate text-[11px] font-medium text-ink">{{ task }}</p>
                                    <div class="mt-1.5 flex items-center gap-1"><span class="size-3.5 rounded-full bg-sunken ring-1 ring-hairline" /><span class="h-1.5 w-8 rounded-full bg-ink/10" /></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="order-1 lg:order-2">
                        <p class="text-sm font-semibold text-[var(--brand)]">Projects & collaboration</p>
                        <h3 class="mt-2 text-[clamp(1.5rem,3vw,2rem)] font-semibold tracking-[-0.02em]">Deliver the work after you win it</h3>
                        <p class="mt-3 text-muted">Spin a delivery project off a won deal — it inherits the customer. Kanban tasks with subtasks, time tracking that rolls up, and file attachments, all on a shared activity feed.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CAPABILITIES -->
        <section id="capabilities" class="border-t border-hairline bg-surface/40 px-5 py-20 lg:px-8">
            <div class="mx-auto max-w-[1180px]">
                <div v-reveal class="mb-12 max-w-2xl">
                    <p class="text-sm font-semibold text-[var(--brand)]">Everything included</p>
                    <h2 class="mt-2 text-[clamp(1.8rem,4vw,2.6rem)] font-semibold tracking-[-0.025em]">A complete platform, out of the box</h2>
                    <p class="mt-3 text-muted">No add-ons to buy. Every workspace gets the full surface — turn on what you need.</p>
                </div>
                <div class="grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(g, gi) in groups" :key="g.name" v-reveal="(gi % 3) * 60">
                        <div class="flex items-center gap-2.5">
                            <span class="grid size-8 place-items-center rounded-lg brand-wash text-[var(--brand)]">
                                <svg viewBox="0 0 24 24" fill="none" class="size-[18px]" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path :d="groupIcon(gi)" /></svg>
                            </span>
                            <h3 class="text-[15px] font-semibold tracking-[-0.01em]">{{ g.name }}</h3>
                        </div>
                        <ul class="mt-3 space-y-2 pl-0.5">
                            <li v-for="item in g.items" :key="item" class="flex items-start gap-2.5 text-sm text-ink-soft">
                                <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 size-4 shrink-0 text-[var(--brand)]" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5" /></svg>
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- MODULES -->
        <section id="modules" class="px-5 py-20 lg:px-8">
            <div class="mx-auto grid max-w-[1180px] items-center gap-10 lg:grid-cols-[1fr_1.1fr]">
                <div v-reveal>
                    <p class="text-sm font-semibold text-[var(--brand)]">Industry modules</p>
                    <h2 class="mt-2 text-[clamp(1.7rem,3.5vw,2.4rem)] font-semibold tracking-[-0.025em]">Tailored to your business</h2>
                    <p class="mt-3 text-muted">Install a vertical and it adds its own records — fully tenant-scoped and linked to your contacts. More can be added without touching core.</p>
                    <Link href="/register" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-[var(--brand)] hover:underline">
                        Explore modules
                        <svg viewBox="0 0 24 24" fill="none" class="size-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </Link>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div v-for="(m, i) in [['Real Estate','M3 11l9-8 9 8M5 9v11h14V9'],['Recruitment','M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M11 7a4 4 0 11-8 0 4 4 0 018 0z'],['Education','M22 10L12 5 2 10l10 5 10-5zM6 12v5c0 1 3 2 6 2s6-1 6-2v-5'],['Healthcare','M12 21s-8-4.5-8-11a4.5 4.5 0 018-2.8A4.5 4.5 0 0120 10c0 6.5-8 11-8 11z']]" :key="m[0]"
                         v-reveal="i * 70" class="group rounded-2xl border border-hairline bg-surface p-6 shadow-e1 transition-all hover:-translate-y-1 hover:shadow-e2">
                        <svg viewBox="0 0 24 24" fill="none" class="size-6 text-[var(--brand)]" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path :d="m[1]" /></svg>
                        <p class="mt-4 text-[15px] font-semibold tracking-[-0.01em]">{{ m[0] }}</p>
                        <p class="mt-1 text-xs text-muted">Installable</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="px-5 py-16 lg:px-8">
            <div v-reveal class="relative mx-auto max-w-[1100px] overflow-hidden rounded-3xl p-12 text-center text-white shadow-e3"
                 :style="{ background: 'linear-gradient(135deg, var(--brand), color-mix(in srgb, var(--brand) 58%, #09090b))' }">
                <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 24px 24px;" />
                <h2 class="relative text-[clamp(1.9rem,4vw,2.8rem)] font-semibold tracking-[-0.025em]">Bring your whole pipeline together.</h2>
                <p class="relative mx-auto mt-3 max-w-lg text-white/80">Create a workspace in under a minute. Invite your team. No credit card required.</p>
                <Link href="/register" class="relative mt-8 inline-flex items-center gap-2 rounded-[var(--radius-control)] bg-white px-6 py-3 text-sm font-semibold text-ink shadow-e2 transition-transform hover:-translate-y-0.5">
                    Get started free
                    <svg viewBox="0 0 24 24" fill="none" class="size-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                </Link>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t border-hairline px-5 py-10 lg:px-8">
            <div class="mx-auto flex max-w-[1180px] flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-7 place-items-center rounded-lg text-xs font-bold text-white" :style="{ background: 'var(--brand)' }">{{ appName[0] }}</span>
                    <span class="text-sm font-medium text-muted">© {{ appName }} — the all-in-one CRM for growing teams.</span>
                </div>
                <div class="flex gap-6 text-sm text-muted">
                    <a href="#capabilities" class="hover:text-ink">Capabilities</a>
                    <Link href="/login" class="hover:text-ink">Sign in</Link>
                    <Link href="/register" class="hover:text-ink">Create workspace</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes rise-kf { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: none; } }
.rise { opacity: 0; animation: rise-kf 0.75s cubic-bezier(0.2, 0.7, 0.3, 1) forwards; }

.reveal { opacity: 0; transform: translateY(22px); transition: opacity 0.75s cubic-bezier(0.2,0.7,0.3,1), transform 0.75s cubic-bezier(0.2,0.7,0.3,1); }
.reveal.is-visible { opacity: 1; transform: none; }

@keyframes float-kf { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
.float { animation: float-kf 7s ease-in-out infinite; animation-delay: 1.4s; }

/* Pipeline card flow (TransitionGroup) */
.flow-move { transition: transform 0.55s cubic-bezier(0.2,0.7,0.3,1); }
.flow-enter-active { transition: opacity 0.45s ease, transform 0.45s cubic-bezier(0.2,0.7,0.3,1); }
.flow-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; position: absolute; left: 0; right: 0; }
.flow-enter-from { opacity: 0; transform: translateX(-16px) scale(0.95); }
.flow-leave-to { opacity: 0; transform: translateX(16px) scale(0.95); }

/* Analytics bars grow on reveal */
.chart .bar { height: 0; transition: height 0.7s cubic-bezier(0.2,0.7,0.3,1); }
.chart.is-visible .bar, .reveal.is-visible .chart .bar { height: var(--h); }

.aurora { position: absolute; border-radius: 9999px; filter: blur(90px); }
.aurora-a { top: -140px; right: -40px; width: 480px; height: 480px; opacity: 0.16; animation: drift-a 20s ease-in-out infinite; }
.aurora-b { top: 60px; left: -140px; width: 400px; height: 400px; background: #8b5cf6; opacity: 0.12; animation: drift-b 24s ease-in-out infinite; }
@keyframes drift-a { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-50px, 36px); } }
@keyframes drift-b { 0%,100% { transform: translate(0,0); } 50% { transform: translate(60px, -24px); } }

@media (prefers-reduced-motion: reduce) {
    .rise, .float, .aurora { animation: none; }
    .rise { opacity: 1; transform: none; }
    .reveal { opacity: 1; transform: none; transition: none; }
    .flow-move, .flow-enter-active, .flow-leave-active { transition: none; }
    .chart .bar { height: var(--h); transition: none; }
}
</style>
