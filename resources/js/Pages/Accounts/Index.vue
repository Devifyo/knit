<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button, DataTable, Tag, Modal, Input } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

const props = defineProps({ accounts: Array, companies: { type: Array, default: () => [] } });
const { can } = usePermissions();

const columns = [
    { key: 'company', label: 'Account', sortable: true },
    { key: 'industry', label: 'Industry' },
    { key: 'health_score', label: 'Health', align: 'right', mono: true },
    { key: 'renewal_date', label: 'Renewal' },
    { key: 'renewal_status', label: 'Status' },
];

const open = ref(false);
const form = useForm({ company_id: '', health_score: '', renewal_date: '', renewal_status: '' });
const submit = () => form.post('/accounts', { preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); } });
</script>

<template>
    <Head title="Accounts" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Accounts</h1>
                <p class="mt-1 text-sm text-muted">Enterprise relationships, health and renewals</p>
            </div>
            <Button v-if="can('accounts.manage')" @click="open = true">New account</Button>
        </div>

        <DataTable :columns="columns" :rows="accounts" empty-title="No accounts yet" empty-description="Wrap a key company as an account to track its health and renewal.">
            <template #cell:health_score="{ value }">
                <span class="nums" :class="value >= 70 ? 'text-positive' : value >= 40 ? 'text-warning' : 'text-critical'">{{ value }}</span>
            </template>
            <template #cell:renewal_status="{ value }">
                <Tag v-if="value" size="sm" :color="value === 'churned' ? 'critical' : value === 'renewed' ? 'positive' : 'warning'">{{ value }}</Tag>
                <span v-else class="text-muted">—</span>
            </template>
        </DataTable>

        <Modal :open="open" title="New account" @close="open = false">
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Company</label>
                    <select v-model="form.company_id" class="h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
                        <option value="" disabled>Select a company…</option>
                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <p v-if="form.errors.company_id" class="mt-1 text-xs text-critical">{{ form.errors.company_id }}</p>
                    <p v-if="!companies.length" class="mt-1 text-xs text-muted">No companies are available to wrap yet — <Link href="/companies" class="text-[var(--brand)]">add a company first</Link>. (Companies that already have an account aren't listed.)</p>
                </div>
                <Input v-model="form.health_score" type="number" label="Health score (0–100)" placeholder="80" :error="form.errors.health_score" />
                <Input v-model="form.renewal_date" type="date" label="Renewal date" :error="form.errors.renewal_date" />
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Renewal status</label>
                    <select v-model="form.renewal_status" class="h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
                        <option value="">—</option>
                        <option value="pending">pending</option>
                        <option value="at risk">at risk</option>
                        <option value="renewed">renewed</option>
                        <option value="churned">churned</option>
                    </select>
                </div>
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" :disabled="!form.company_id" @click="submit">Create account</Button>
            </template>
        </Modal>
    </div>
</template>
