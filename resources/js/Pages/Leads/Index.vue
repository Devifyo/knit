<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button, Input, DataTable, Tag, Modal } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

defineProps({ leads: Array });
const { can } = usePermissions();

const open = ref(false);
const form = useForm({ name: '', email: '', phone: '', source: '' });
const submit = () => form.post('/leads', { preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); } });

const convert = (id) => router.post(`/leads/${id}/convert`);

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

        <DataTable :columns="columns" :rows="leads" empty-title="No leads yet" empty-description="Capture your first lead to start the pipeline.">
            <template #cell:score="{ value }">
                <span class="nums" :class="value >= 60 ? 'text-positive' : value >= 30 ? 'text-warning' : 'text-muted'">{{ value }}</span>
            </template>
            <template #cell:status="{ row }">
                <Tag v-if="row.converted" size="sm" color="positive" dot>converted</Tag>
                <Tag v-else size="sm" :color="statusColor(row.status)">{{ row.status }}</Tag>
            </template>
            <template #cell:actions="{ row }">
                <Button v-if="!row.converted && can('leads.convert')" variant="secondary" size="sm" @click="convert(row.id)">Convert</Button>
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
