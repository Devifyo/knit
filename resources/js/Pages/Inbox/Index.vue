<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Card, Tag, Button, Input, Modal, Avatar, EmptyState } from '@/Components/ui';
import { useTenant } from '@/Composables/useTenant';
import { useEcho } from '@/Composables/useEcho';

const props = defineProps({ conversations: Array, filter: String });
const { tenant } = useTenant();

const tabs = [{ k: 'open', l: 'Open' }, { k: 'mine', l: 'Assigned to me' }, { k: 'all', l: 'All' }];
const setFilter = (k) => router.get('/inbox', { filter: k }, { preserveState: true, replace: true });

const open = ref(false);
const sim = useForm({ from_email: 'jordan@northwind.test', subject: 'Question about pricing', body: 'Hi — can you send me your enterprise pricing?' });
const simulate = () => sim.post('/inbox/simulate', { onSuccess: () => (open.value = false) });

onMounted(() => {
    const id = tenant.value?.id;
    if (id) useEcho(`tenant.${id}.inbox`, '.NewInboundMessage', () => router.reload({ only: ['conversations'] }));
});
</script>

<template>
    <Head title="Inbox" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Shared inbox</h1>
                <p class="mt-1 text-sm text-muted">Email conversations for the whole team</p>
            </div>
            <Button variant="secondary" @click="open = true">Simulate inbound email</Button>
        </div>

        <div class="flex gap-1">
            <button v-for="t in tabs" :key="t.k" :class="['rounded-[var(--radius-control)] px-3 py-1.5 text-sm font-medium', filter === t.k ? 'brand-wash text-[var(--brand)]' : 'text-muted hover:bg-sunken']" @click="setFilter(t.k)">{{ t.l }}</button>
        </div>

        <Card flush>
            <ul v-if="conversations.length" class="divide-y divide-hairline-soft">
                <li v-for="c in conversations" :key="c.id">
                    <Link :href="`/inbox/${c.id}`" class="flex items-center gap-3 px-5 py-3.5 hover:bg-sunken/60">
                        <Avatar :name="c.contact || c.subject" size="sm" />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-medium text-ink" :class="{ 'font-semibold': c.unread }">{{ c.subject }}</p>
                                <span v-if="c.unread" class="rounded-full bg-[var(--brand)] px-1.5 text-[10px] font-semibold text-white">{{ c.unread }}</span>
                            </div>
                            <p class="truncate text-xs text-muted">{{ c.contact || 'Unknown sender' }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <Tag size="sm" :color="c.status === 'open' ? 'info' : 'neutral'">{{ c.status }}</Tag>
                            <span v-if="c.assignee" class="text-xs text-faint">{{ c.assignee }}</span>
                            <span class="text-xs text-faint">{{ c.last_at }}</span>
                        </div>
                    </Link>
                </li>
            </ul>
            <EmptyState v-else title="Inbox zero" description="No conversations in this view. Use “Simulate inbound email” to see threading in action." />
        </Card>

        <Modal :open="open" title="Simulate inbound email" @close="open = false">
            <p class="mb-4 text-sm text-muted">No mail provider in dev — this posts an email as if it arrived. If the sender matches a contact, it links + lands on their timeline.</p>
            <form class="space-y-4" @submit.prevent="simulate">
                <Input v-model="sim.from_email" type="email" label="From" :error="sim.errors.from_email" />
                <Input v-model="sim.subject" label="Subject" :error="sim.errors.subject" />
                <Input v-model="sim.body" label="Body" :error="sim.errors.body" />
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="sim.processing" @click="simulate">Receive email</Button>
            </template>
        </Modal>
    </div>
</template>
