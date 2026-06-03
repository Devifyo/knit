<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Card, Button, Input, Tag, Modal, EmptyState } from '@/Components/ui';

defineOptions({ layout: PortalLayout });
defineProps({ tickets: Array });

const statusColor = (s) => ({ open: 'info', pending: 'warning', resolved: 'positive', closed: 'neutral' }[s] || 'neutral');
const selStyle = 'h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';

const open = ref(false);
const form = useForm({ subject: '', body: '', priority: 'normal' });
const submit = () => form.post('/portal/tickets', { onSuccess: () => { open.value = false; form.reset(); } });
</script>

<template>
    <Head title="Tickets" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Support tickets</h1>
            <Button @click="open = true">New ticket</Button>
        </div>

        <Card v-if="tickets.length" flush>
            <ul class="divide-y divide-hairline-soft">
                <li v-for="t in tickets" :key="t.id">
                    <Link :href="`/portal/tickets/${t.id}`" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-sunken">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink">{{ t.subject }}</p>
                            <p class="font-mono text-xs text-faint">{{ t.number }} · {{ t.updated_at }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <Tag size="sm">{{ t.priority }}</Tag>
                            <Tag size="sm" :color="statusColor(t.status)">{{ t.status }}</Tag>
                        </div>
                    </Link>
                </li>
            </ul>
        </Card>
        <Card v-else flush>
            <EmptyState title="No tickets yet" description="Open a ticket and our team will get back to you.">
                <template #action><Button @click="open = true">New ticket</Button></template>
            </EmptyState>
        </Card>

        <Modal :open="open" title="New ticket" @close="open = false">
            <form class="space-y-4" @submit.prevent="submit">
                <Input v-model="form.subject" label="Subject" :error="form.errors.subject" />
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Message</label>
                    <textarea v-model="form.body" rows="5" :class="selStyle" class="!h-auto py-2" placeholder="How can we help?"></textarea>
                    <p v-if="form.errors.body" class="mt-1 text-xs text-critical">{{ form.errors.body }}</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Priority</label>
                    <select v-model="form.priority" :class="selStyle">
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">Submit ticket</Button>
            </template>
        </Modal>
    </div>
</template>
