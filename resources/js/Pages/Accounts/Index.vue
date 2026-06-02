<script setup>
import { Head } from '@inertiajs/vue3';
import { DataTable, Tag } from '@/Components/ui';

defineProps({ accounts: Array });

const columns = [
    { key: 'company', label: 'Account', sortable: true },
    { key: 'industry', label: 'Industry' },
    { key: 'health_score', label: 'Health', align: 'right', mono: true },
    { key: 'renewal_date', label: 'Renewal' },
    { key: 'renewal_status', label: 'Status' },
];
</script>

<template>
    <Head title="Accounts" />
    <div class="space-y-5">
        <div>
            <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Accounts</h1>
            <p class="mt-1 text-sm text-muted">Enterprise relationships, health and renewals</p>
        </div>
        <DataTable :columns="columns" :rows="accounts" empty-title="No accounts yet" empty-description="Accounts wrap your key companies with health and renewal tracking.">
            <template #cell:health_score="{ value }">
                <span class="nums" :class="value >= 70 ? 'text-positive' : value >= 40 ? 'text-warning' : 'text-critical'">{{ value }}</span>
            </template>
            <template #cell:renewal_status="{ value }">
                <Tag v-if="value" size="sm" :color="value === 'churned' ? 'critical' : value === 'renewed' ? 'positive' : 'warning'">{{ value }}</Tag>
                <span v-else class="text-muted">—</span>
            </template>
        </DataTable>
    </div>
</template>
