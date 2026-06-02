<script setup>
import { Head } from '@inertiajs/vue3';
import { Card } from '@/Components/ui';

defineProps({ kpis: Object, byStage: Array });

const cards = (kpis) => [
    { label: 'Open deals', value: kpis.open_deals, hint: 'in pipeline' },
    { label: 'Pipeline value', value: kpis.pipeline_value, hint: 'open, weighted next' },
    { label: 'New leads', value: kpis.new_leads, hint: 'last 7 days' },
    { label: 'Contacts', value: kpis.contacts, hint: 'total' },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Overview</h1>
            <p class="mt-1 text-sm text-muted">A live snapshot of your workspace.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card v-for="kpi in cards(kpis)" :key="kpi.label">
                <p class="text-xs font-medium text-muted">{{ kpi.label }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.02em] text-ink nums">{{ kpi.value }}</p>
                <p class="mt-1 text-xs text-faint">{{ kpi.hint }}</p>
            </Card>
        </div>

        <Card title="Pipeline by stage" subtitle="Open deals distributed across your sales stages">
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
    </div>
</template>
