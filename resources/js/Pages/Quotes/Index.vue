<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button, DataTable, Tag, Modal, Input } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

defineProps({ quotes: Array, products: Array });
const { can } = usePermissions();

const open = ref(false);
const form = useForm({ currency: 'USD', tax_rate: 0 });
const submit = () => form.post('/quotes', { onSuccess: () => { open.value = false; form.reset(); } });

const columns = [
    { key: 'number', label: 'Quote', sortable: true, mono: true },
    { key: 'items', label: 'Items', align: 'right', mono: true },
    { key: 'currency', label: 'Currency' },
    { key: 'total', label: 'Total', align: 'right', mono: true },
    { key: 'status', label: 'Status' },
];
const statusColor = (s) => ({ draft: 'neutral', sent: 'info', accepted: 'positive', declined: 'critical' }[s] ?? 'neutral');
</script>

<template>
    <Head title="Quotes" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Quotes</h1>
                <p class="mt-1 text-sm text-muted">Build proposals and export branded PDFs</p>
            </div>
            <Button v-if="can('quotes.manage')" @click="open = true">New quote</Button>
        </div>

        <DataTable :columns="columns" :rows="quotes" clickable empty-title="No quotes yet" empty-description="Create a quote, add line items, then download a branded PDF." @row-click="(r) => router.visit(`/quotes/${r.id}`)">
            <template #cell:status="{ value }"><Tag size="sm" :color="statusColor(value)">{{ value }}</Tag></template>
        </DataTable>

        <Modal :open="open" title="New quote" @close="open = false">
            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted">Currency</label>
                        <select v-model="form.currency" class="h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
                            <option>USD</option><option>EUR</option><option>GBP</option><option>INR</option>
                        </select>
                    </div>
                    <Input v-model="form.tax_rate" type="number" label="Tax rate (%)" />
                </div>
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">Create quote</Button>
            </template>
        </Modal>
    </div>
</template>
