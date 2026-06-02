<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Card, Tag, Button, Input, Modal, EmptyState } from '@/Components/ui';

defineProps({ tickets: Array, filter: String });

const tabs = [{ k: 'open', l: 'Open' }, { k: 'breached', l: 'Escalated' }, { k: 'all', l: 'All' }];
const setFilter = (k) => router.get('/tickets', { filter: k }, { preserveState: true, replace: true });

const open = ref(false);
const sim = useForm({ from_email: 'pat@customer.test', subject: 'Cannot log in', body: 'I am locked out of my account.', priority: 'high' });
const simulate = () => sim.post('/tickets/simulate', { onSuccess: () => (open.value = false) });

const prioColor = (p) => ({ urgent: 'critical', high: 'warning', normal: 'info', low: 'neutral' }[p] ?? 'neutral');
const statusColor = (s) => ({ open: 'info', pending: 'warning', resolved: 'positive', closed: 'neutral' }[s] ?? 'neutral');
const selStyle = 'h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';
</script>

<template>
    <Head title="Tickets" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Support tickets</h1>
                <p class="mt-1 text-sm text-muted">SLA-timed, auto-routed customer issues</p>
            </div>
            <Button variant="secondary" @click="open = true">Simulate inbound ticket</Button>
        </div>

        <div class="flex gap-1">
            <button v-for="t in tabs" :key="t.k" :class="['rounded-[var(--radius-control)] px-3 py-1.5 text-sm font-medium', filter === t.k ? 'brand-wash text-[var(--brand)]' : 'text-muted hover:bg-sunken']" @click="setFilter(t.k)">{{ t.l }}</button>
        </div>

        <Card flush>
            <ul v-if="tickets.length" class="divide-y divide-hairline-soft">
                <li v-for="t in tickets" :key="t.id">
                    <Link :href="`/tickets/${t.id}`" class="flex items-center gap-3 px-5 py-3.5 hover:bg-sunken/60">
                        <Tag size="sm" :color="prioColor(t.priority)" dot>{{ t.priority }}</Tag>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-ink">{{ t.subject }}</p>
                            <p class="truncate text-xs text-muted"><span class="nums">{{ t.number }}</span> · {{ t.contact || 'Unknown' }} · {{ t.channel }}</p>
                        </div>
                        <Tag v-if="t.breached" size="sm" color="critical">escalated</Tag>
                        <span class="text-xs" :class="t.overdue ? 'font-medium text-critical' : 'text-faint'">{{ t.overdue ? 'SLA overdue' : t.due }}</span>
                        <Tag size="sm" :color="statusColor(t.status)">{{ t.status }}</Tag>
                        <span v-if="t.assignee" class="shrink-0 text-xs text-faint">{{ t.assignee }}</span>
                    </Link>
                </li>
            </ul>
            <EmptyState v-else title="No tickets here" description="Use “Simulate inbound ticket” to see routing + SLA + escalation in action." />
        </Card>

        <Modal :open="open" title="Simulate inbound support email" @close="open = false">
            <p class="mb-4 text-sm text-muted">Creates a ticket as if an email arrived — links the sender to a contact, starts the SLA timer by priority, and auto-assigns the least-busy agent.</p>
            <form class="space-y-4" @submit.prevent="simulate">
                <Input v-model="sim.from_email" type="email" label="From" :error="sim.errors.from_email" />
                <Input v-model="sim.subject" label="Subject" :error="sim.errors.subject" />
                <Input v-model="sim.body" label="Body" :error="sim.errors.body" />
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Priority</label>
                    <select v-model="sim.priority" :class="selStyle"><option>low</option><option>normal</option><option>high</option><option>urgent</option></select>
                </div>
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="sim.processing" @click="simulate">Create ticket</Button>
            </template>
        </Modal>
    </div>
</template>
