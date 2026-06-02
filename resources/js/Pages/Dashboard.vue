<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Card, Tag, Kanban } from '@/Components/ui';

const kpis = [
    { label: 'Open deals', value: '—', delta: 'Phase 2' },
    { label: 'Pipeline value', value: '—', delta: 'Phase 2' },
    { label: 'New leads (7d)', value: '—', delta: 'Phase 2' },
    { label: 'Open tickets', value: '—', delta: 'Phase 5' },
];

const columns = ref([
    { id: 'new', title: 'New', cards: [{ id: 1, title: 'Acme Corp — sample card' }] },
    { id: 'qualified', title: 'Qualified', cards: [{ id: 2, title: 'Globex — sample card' }] },
    { id: 'won', title: 'Won', cards: [] },
]);
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card v-for="kpi in kpis" :key="kpi.label">
                <p class="text-xs font-medium text-gray-500">{{ kpi.label }}</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ kpi.value }}</p>
                <Tag color="gray" size="sm" class="mt-2">{{ kpi.delta }}</Tag>
            </Card>
        </div>

        <Card title="Pipeline (Kanban demo)" subtitle="Drag-and-drop wired; live updates land in Phase 2 via Reverb">
            <Kanban :columns="columns" />
        </Card>
    </div>
</template>
