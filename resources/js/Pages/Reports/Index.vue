<script setup>
import { reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Card, Button, DataTable } from '@/Components/ui';

const props = defineProps({ filters: Object, report: Object, owners: Array });

const f = reactive({
    entity: props.filters.entity ?? 'deals',
    owner_id: props.filters.owner_id ?? '',
    status: props.filters.status ?? '',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
});

const apply = () => router.get('/reports', f, { preserveState: true, replace: true });
const exportUrl = (fmt) => '/reports/export/' + fmt + '?' + new URLSearchParams(Object.fromEntries(Object.entries(f).filter(([, v]) => v))).toString();

const numericHeaders = ['Amount', 'Score', 'Health', 'Contacts', 'Recipients'];
const columns = (headers) => headers.map((h, i) => ({ key: String(i), label: h, align: numericHeaders.includes(h) ? 'right' : 'left' }));
const tableRows = (rows) => rows.map((r, ri) => ({ id: ri, ...Object.fromEntries(r.map((c, i) => [String(i), c])) }));
const selStyle = 'h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';
const statusOpts = {
    deals: ['', 'open', 'won', 'lost'],
    leads: ['', 'new', 'working', 'qualified', 'unqualified'],
    contacts: [''],
    companies: [''],
    tickets: ['', 'open', 'pending', 'resolved', 'closed'],
    campaigns: ['', 'draft', 'sending', 'sent'],
};
// Owner filter only applies to entities that have an owner column.
const ownerEntities = ['deals', 'leads', 'contacts', 'companies', 'tickets'];
</script>

<template>
    <Head title="Reports" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Reports</h1>
                <p class="mt-1 text-sm text-muted">Filter by owner and date, then export</p>
            </div>
            <div class="flex gap-2">
                <Button variant="secondary" :href="exportUrl('csv')">CSV</Button>
                <Button variant="secondary" :href="exportUrl('xlsx')">Excel</Button>
                <Button variant="secondary" :href="exportUrl('pdf')">PDF</Button>
            </div>
        </div>

        <Card>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Report</label>
                    <select v-model="f.entity" :class="selStyle" @change="f.status = ''">
                        <option value="deals">Deals</option>
                        <option value="leads">Leads</option>
                        <option value="contacts">Contacts</option>
                        <option value="companies">Companies</option>
                        <option value="tickets">Tickets</option>
                        <option value="campaigns">Campaigns</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Owner</label>
                    <select v-model="f.owner_id" :class="selStyle" :disabled="!ownerEntities.includes(f.entity)"><option value="">Anyone</option><option v-for="o in owners" :key="o.id" :value="o.id">{{ o.name }}</option></select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Status</label>
                    <select v-model="f.status" :class="selStyle"><option v-for="s in statusOpts[f.entity]" :key="s" :value="s">{{ s || 'Any' }}</option></select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">From</label>
                    <input v-model="f.from" type="date" :class="selStyle" />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">To</label>
                    <input v-model="f.to" type="date" :class="selStyle" />
                </div>
            </div>
            <div class="mt-4"><Button @click="apply">Run report</Button></div>
        </Card>

        <div class="flex flex-wrap gap-3">
            <Card v-for="(value, label) in report.summary" :key="label" class="flex-1">
                <p class="text-xs font-medium text-muted">{{ label }}</p>
                <p class="mt-1 text-xl font-semibold tracking-[-0.02em] text-ink nums">{{ value }}</p>
            </Card>
        </div>

        <DataTable :columns="columns(report.headers)" :rows="tableRows(report.rows)" :empty-title="`No ${f.entity} match these filters`" />
    </div>
</template>
