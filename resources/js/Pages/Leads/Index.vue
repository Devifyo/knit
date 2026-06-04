<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button, Input, DataTable, Tag, Modal } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

const props = defineProps({ leads: Array, captureUrl: String });
const { can } = usePermissions();

const copyCapture = () => navigator.clipboard?.writeText(props.captureUrl);

const open = ref(false);
const form = useForm({ name: '', email: '', phone: '', source: '' });
const submit = () => form.post('/leads', { preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); } });

const convert = (id) => router.post(`/leads/${id}/convert`);
const scoreAi = (id) => router.post(`/leads/${id}/score`, {}, { preserveScroll: true });

const columns = [
    { key: 'name', label: 'Lead', sortable: true },
    { key: 'email', label: 'Email' },
    { key: 'source', label: 'Source' },
    { key: 'score', label: 'Score', align: 'right', mono: true },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];
const statusColor = (s) => ({ new: 'info', working: 'warning', qualified: 'positive', unqualified: 'neutral' }[s] ?? 'neutral');
</script>

<template>
    <Head title="Leads" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Leads</h1>
                <p class="mt-1 text-sm text-muted">Capture, qualify and convert</p>
            </div>
            <Button v-if="can('leads.manage')" @click="open = true">Capture lead</Button>
        </div>

        <div class="flex flex-wrap items-center gap-2 rounded-[var(--radius-card)] border border-hairline bg-surface px-4 py-3 text-sm shadow-e1">
            <span class="text-muted">Public capture form:</span>
            <a :href="captureUrl" target="_blank" class="font-mono text-[var(--brand)] hover:underline">{{ captureUrl }}</a>
            <button class="ml-auto rounded-md px-2 py-1 text-xs text-ink-soft ring-1 ring-hairline hover:bg-sunken" @click="copyCapture">Copy link</button>
            <span class="text-xs text-faint">Submissions create a lead and fire your <code>lead.created</code> workflows.</span>
        </div>

        <DataTable :columns="columns" :rows="leads" empty-title="No leads yet" empty-description="Capture your first lead to start the pipeline.">
            <template #cell:name="{ row }">
                <Link :href="`/leads/${row.id}`" class="font-medium text-ink hover:text-[var(--brand)] hover:underline">{{ row.name }}</Link>
            </template>
            <template #cell:source="{ row }">
                <div class="flex items-center gap-1.5">
                    <span class="text-ink-soft">{{ row.source || '—' }}</span>
                    <a v-if="row.source_url" :href="row.source_url" target="_blank" :title="row.source_url" class="text-faint hover:text-[var(--brand)]" @click.stop>
                        <svg viewBox="0 0 24 24" fill="none" class="size-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3" /></svg>
                    </a>
                </div>
            </template>
            <template #cell:score="{ value }">
                <span class="nums" :class="value >= 60 ? 'text-positive' : value >= 30 ? 'text-warning' : 'text-muted'">{{ value }}</span>
            </template>
            <template #cell:status="{ row }">
                <Tag v-if="row.converted" size="sm" color="positive" dot>converted</Tag>
                <Tag v-else size="sm" :color="statusColor(row.status)">{{ row.status }}</Tag>
            </template>
            <template #cell:actions="{ row }">
                <div class="flex items-center justify-end gap-1.5">
                    <Button v-if="can('leads.manage')" variant="ghost" size="sm" @click="scoreAi(row.id)">Score with AI</Button>
                    <Button v-if="!row.converted && can('leads.convert')" variant="secondary" size="sm" @click="convert(row.id)">Convert</Button>
                </div>
            </template>
        </DataTable>

        <Modal :open="open" title="Capture lead" @close="open = false">
            <form class="space-y-4" @submit.prevent="submit">
                <Input v-model="form.name" label="Name" :error="form.errors.name" />
                <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" />
                <div class="grid grid-cols-2 gap-3">
                    <Input v-model="form.phone" label="Phone" />
                    <Input v-model="form.source" label="Source" placeholder="Website" />
                </div>
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">Capture</Button>
            </template>
        </Modal>
    </div>
</template>
