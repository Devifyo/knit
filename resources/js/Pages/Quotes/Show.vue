<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button, Card, Input, DataTable, Tag } from '@/Components/ui';

const props = defineProps({ quote: Object, totals: Object, products: Array });

const setStatus = (status) => router.patch(`/quotes/${props.quote.id}/status`, { status }, { preserveScroll: true });
const statusColor = (s) => ({ draft: 'neutral', sent: 'info', accepted: 'positive', declined: 'critical' }[s] ?? 'neutral');

const item = useForm({ name: '', quantity: 1, unit_price: '', discount_pct: 0 });
const addItem = () => item.post(`/quotes/${props.quote.id}/items`, { preserveScroll: true, onSuccess: () => item.reset() });

const pickProduct = (e) => {
    const p = props.products.find((x) => x.id === Number(e.target.value));
    if (p) { item.name = p.name; item.unit_price = (p.unit_price / 100).toFixed(2); }
};

const columns = [
    { key: 'name', label: 'Item' },
    { key: 'quantity', label: 'Qty', align: 'right', mono: true },
    { key: 'unit_price', label: 'Unit', align: 'right', mono: true },
    { key: 'discount_pct', label: 'Disc.', align: 'right', mono: true },
    { key: 'line_total', label: 'Amount', align: 'right', mono: true },
];
const money = (minor) => (minor / 100).toFixed(2);
</script>

<template>
    <Head :title="quote.number" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <Link href="/quotes" class="text-sm text-muted hover:text-ink-soft">← Quotes</Link>
                <div class="mt-1 flex items-center gap-2">
                    <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink nums">{{ quote.number }}</h1>
                    <Tag :color="statusColor(quote.status)" size="sm">{{ quote.status }}</Tag>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button v-if="quote.status === 'draft'" variant="secondary" @click="setStatus('sent')">Mark sent</Button>
                <Button v-if="quote.status === 'sent'" variant="secondary" @click="setStatus('accepted')">Mark accepted</Button>
                <Button :href="`/quotes/${quote.id}/pdf`">Download PDF</Button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_320px]">
            <div class="space-y-5">
                <DataTable :columns="columns" :rows="quote.items" empty-title="No line items">
                    <template #cell:unit_price="{ value }">{{ money(value) }}</template>
                    <template #cell:discount_pct="{ value }">{{ value }}%</template>
                </DataTable>

                <Card title="Add line item">
                    <form class="space-y-3" @submit.prevent="addItem">
                        <div v-if="products.length">
                            <label class="mb-1.5 block text-xs font-medium text-muted">From catalog</label>
                            <select class="h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]" @change="pickProduct">
                                <option value="">— custom —</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <Input v-model="item.name" label="Name" :error="item.errors.name" />
                        <div class="grid grid-cols-3 gap-3">
                            <Input v-model="item.quantity" type="number" label="Qty" />
                            <Input v-model="item.unit_price" type="number" label="Unit price" />
                            <Input v-model="item.discount_pct" type="number" label="Discount %" />
                        </div>
                        <Button :loading="item.processing" @click="addItem">Add item</Button>
                    </form>
                </Card>
            </div>

            <Card title="Summary">
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd class="nums text-ink">{{ totals.subtotal_formatted }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Tax ({{ totals.tax_rate }}%)</dt><dd class="nums text-ink">{{ totals.tax_formatted }}</dd></div>
                    <div class="flex justify-between border-t border-hairline pt-2.5 text-base font-semibold"><dt class="text-ink">Total</dt><dd class="nums text-ink">{{ totals.total_formatted }}</dd></div>
                </dl>
            </Card>
        </div>
    </div>
</template>
