<script setup>
import { computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, Tag } from '@/Components/ui';
import { useTenant } from '@/Composables/useTenant';
import { useEcho } from '@/Composables/useEcho';
import { usePermissions } from '@/Composables/usePermissions';

const props = defineProps({ kpis: Object, won: Object, byStage: Array, leaderboard: Array });
const { tenant } = useTenant();
const { can } = usePermissions();

// Until the workspace adds its own SMTP, email uses the platform default.
const mailNeedsSetup = computed(() => tenant.value?.mail_configured === false && can('settings.update'));

// Live: refresh widgets when a workspace metric changes (e.g. a deal closes).
onMounted(() => {
    const id = tenant.value?.id;
    if (id) {
        useEcho(`tenant.${id}.dashboard`, '.StatsUpdated', () =>
            router.reload({ only: ['kpis', 'won', 'byStage', 'leaderboard'] }));
    }
});

const cards = (kpis, won) => [
    { label: 'Open deals', value: kpis.open_deals, hint: 'in pipeline' },
    { label: 'Pipeline value', value: kpis.pipeline_value, hint: 'open' },
    { label: 'Won this month', value: `${won.count} · ${won.revenue}`, hint: `${won.win_rate}% win rate` },
    { label: 'Open tickets', value: won.open_tickets, hint: 'support' },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6">
        <!-- Nudge to configure the workspace's own SMTP (email uses the platform default until then). -->
        <div v-if="mailNeedsSetup" class="flex items-start gap-3 rounded-[var(--radius-card)] border border-warning/30 bg-warning/10 px-4 py-3">
            <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 size-5 shrink-0 text-warning" stroke="currentColor" stroke-width="1.6"><path d="M12 9v4M12 17h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L14.7 3.9a2 2 0 00-3.4 0z" stroke-linecap="round" stroke-linejoin="round" /></svg>
            <div class="flex-1">
                <p class="text-sm font-medium text-ink">Set up email sending for your workspace</p>
                <p class="mt-0.5 text-sm text-muted">You're using Knit's default email server. Add your own SMTP so emails (invites, campaigns, notifications) send from your address.</p>
            </div>
            <Link href="/settings/email" class="shrink-0 self-center rounded-[var(--radius-control)] bg-[var(--brand)] px-3 py-1.5 text-[13px] font-medium text-white transition-opacity hover:opacity-90">Add SMTP</Link>
        </div>

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Overview</h1>
                <p class="mt-1 text-sm text-muted">A live snapshot of your workspace — updates as deals close.</p>
            </div>
            <Tag color="positive" dot>live</Tag>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card v-for="kpi in cards(kpis, won)" :key="kpi.label">
                <p class="text-xs font-medium text-muted">{{ kpi.label }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.02em] text-ink nums">{{ kpi.value }}</p>
                <p class="mt-1 text-xs text-faint">{{ kpi.hint }}</p>
            </Card>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">
            <Card title="Pipeline by stage" subtitle="Open deals across your sales stages">
                <div v-if="byStage.length" class="space-y-3">
                    <div v-for="s in byStage" :key="s.name" class="flex items-center gap-3">
                        <span class="w-28 shrink-0 text-sm text-ink-soft">{{ s.name }}</span>
                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-sunken">
                            <div class="h-full rounded-full bg-[var(--brand)]" :style="{ width: Math.min(100, s.count * 12) + '%', opacity: 0.4 + s.probability / 200 }" />
                        </div>
                        <span class="w-8 shrink-0 text-right text-sm text-muted nums">{{ s.count }}</span>
                    </div>
                </div>
                <p v-else class="text-sm text-muted">No pipeline configured.</p>
            </Card>

            <Card v-if="leaderboard.length" title="Top reps" subtitle="Won revenue this period">
                <ol class="space-y-2.5">
                    <li v-for="(r, i) in leaderboard" :key="r.name" class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2 text-ink-soft"><span class="text-faint nums">{{ i + 1 }}</span> {{ r.name }}</span>
                        <span class="nums text-ink">{{ r.won }}</span>
                    </li>
                </ol>
            </Card>
        </div>
    </div>
</template>
