<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, Tag, Button, Input, Modal, DataTable } from '@/Components/ui';

defineProps({ campaigns: Array });

const open = ref(false);
const form = useForm({ name: '', subject: '', subject_b: '', body: '<p>Hi {{name}}, …</p>', cta_label: 'Learn more', cta_url: 'https://example.com', audience: 'contacts' });
const submit = () => form.post('/campaigns', { onSuccess: () => (open.value = false) });

const columns = [
    { key: 'name', label: 'Campaign', sortable: true },
    { key: 'sent', label: 'Sent', align: 'right', mono: true },
    { key: 'opens', label: 'Opens', align: 'right', mono: true },
    { key: 'clicks', label: 'Clicks', align: 'right', mono: true },
    { key: 'status', label: 'Status' },
];
const statusColor = (s) => ({ draft: 'neutral', sending: 'warning', sent: 'positive' }[s] ?? 'neutral');
const selStyle = 'h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';
</script>

<template>
    <Head title="Campaigns" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Email campaigns</h1>
                <p class="mt-1 text-sm text-muted">Broadcasts with open &amp; click tracking and A/B subjects</p>
            </div>
            <Button @click="open = true">New campaign</Button>
        </div>

        <DataTable :columns="columns" :rows="campaigns" clickable empty-title="No campaigns yet" empty-description="Create a campaign and send it to your contacts." @row-click="(r) => router.visit(`/campaigns/${r.id}`)">
            <template #cell:status="{ value }"><Tag size="sm" :color="statusColor(value)">{{ value }}</Tag></template>
        </DataTable>

        <Modal :open="open" title="New campaign" size="lg" @close="open = false">
            <form class="space-y-4" @submit.prevent="submit">
                <Input v-model="form.name" label="Campaign name" :error="form.errors.name" />
                <div class="grid grid-cols-2 gap-3">
                    <Input v-model="form.subject" label="Subject (A)" :error="form.errors.subject" />
                    <Input v-model="form.subject_b" label="Subject B (optional A/B)" />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Body (HTML)</label>
                    <textarea v-model="form.body" rows="5" class="w-full rounded-[var(--radius-control)] bg-surface p-3 font-mono text-xs text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <Input v-model="form.cta_label" label="CTA label" />
                    <Input v-model="form.cta_url" label="CTA URL (tracked)" />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Audience</label>
                    <select v-model="form.audience" :class="selStyle"><option value="contacts">All contacts</option><option value="leads">All leads</option></select>
                </div>
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">Create</Button>
            </template>
        </Modal>
    </div>
</template>
