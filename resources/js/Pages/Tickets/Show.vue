<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Card, Tag, Button } from '@/Components/ui';

const props = defineProps({ ticket: Object, members: Array });
const page = usePage();
const ai = computed(() => page.props.flash?.ai);
const assist = () => router.post(`/tickets/${props.ticket.id}/assist`, {}, { preserveScroll: true });

const form = useForm({ body: '', internal: false });
const send = () => form.post(`/tickets/${props.ticket.id}/reply`, { preserveScroll: true, onSuccess: () => form.reset() });
const patch = (payload) => router.patch(`/tickets/${props.ticket.id}`, payload, { preserveScroll: true });
const selStyle = 'h-8 rounded-[var(--radius-control)] bg-surface px-2 text-xs text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';
const prioColor = (p) => ({ urgent: 'critical', high: 'warning', normal: 'info', low: 'neutral' }[p] ?? 'neutral');
</script>

<template>
    <Head :title="ticket.number" />
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <Link href="/tickets" class="text-sm text-muted hover:text-ink-soft">← Tickets</Link>
                <div class="mt-1 flex items-center gap-2">
                    <h1 class="text-lg font-semibold tracking-[-0.02em] text-ink">{{ ticket.subject }}</h1>
                    <Tag size="sm" :color="prioColor(ticket.priority)">{{ ticket.priority }}</Tag>
                    <Tag v-if="ticket.escalated" size="sm" color="critical">escalated</Tag>
                </div>
                <p class="text-sm text-muted">
                    <span class="nums">{{ ticket.number }}</span> ·
                    <Link v-if="ticket.contact_id" :href="`/contacts/${ticket.contact_id}`" class="text-[var(--brand)] hover:underline">{{ ticket.contact }}</Link>
                    <span v-else>Unlinked</span> · {{ ticket.channel }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <select :class="selStyle" :value="ticket.priority" @change="patch({ priority: $event.target.value })">
                    <option>low</option><option>normal</option><option>high</option><option>urgent</option>
                </select>
                <select :class="selStyle" :value="''" @change="patch({ assigned_user_id: $event.target.value || null })">
                    <option value="">Unassigned</option>
                    <option v-for="m in members" :key="m.id" :value="m.id" :selected="m.name === ticket.assignee">{{ m.name }}</option>
                </select>
                <select :class="selStyle" :value="ticket.status" @change="patch({ status: $event.target.value })">
                    <option>open</option><option>pending</option><option>resolved</option><option>closed</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_240px]">
            <Card flush>
                <div class="space-y-4 p-5">
                    <div v-for="r in ticket.replies" :key="r.id" class="flex" :class="r.is_agent && !r.is_internal ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[80%] rounded-[var(--radius-card)] px-4 py-2.5 text-sm"
                            :class="r.is_internal ? 'border border-warning/30 bg-warning/10 text-ink-soft' : r.is_agent ? 'bg-[var(--brand)] text-white' : 'bg-sunken text-ink'">
                            <div class="mb-1 flex items-center gap-2 text-[11px]" :class="r.is_agent && !r.is_internal ? 'text-white/70' : 'text-muted'">
                                <span class="font-medium">{{ r.author }}</span>
                                <Tag v-if="r.is_internal" size="sm" color="warning">internal</Tag>
                                <span>· {{ r.at }}</span>
                            </div>
                            <p class="whitespace-pre-wrap">{{ r.body }}</p>
                        </div>
                    </div>
                </div>
                <div class="border-t border-hairline-soft p-4">
                    <textarea v-model="form.body" rows="3" placeholder="Reply to the customer…"
                        class="w-full rounded-[var(--radius-control)] bg-surface p-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]" />
                    <div class="mt-2 flex items-center justify-between">
                        <label class="flex items-center gap-2 text-xs text-muted"><input v-model="form.internal" type="checkbox" class="rounded border-hairline text-[var(--brand)]" /> Internal note</label>
                        <Button :loading="form.processing" @click="send">{{ form.internal ? 'Add note' : 'Send reply' }}</Button>
                    </div>
                </div>
            </Card>

            <Card title="SLA">
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between"><dt class="text-muted">First response</dt><dd :class="ticket.first_responded ? 'text-positive' : 'text-ink-soft'">{{ ticket.first_responded ? 'met' : 'pending' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Due</dt><dd class="text-ink-soft">{{ ticket.sla_due }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Status</dt><dd><Tag size="sm">{{ ticket.status }}</Tag></dd></div>
                    <div v-if="ticket.escalated" class="rounded-lg bg-critical/10 px-2.5 py-1.5 text-xs text-critical">Escalated for SLA breach.</div>
                </dl>
            </Card>

            <Card title="AI assist">
                <Button size="sm" variant="secondary" class="w-full" @click="assist">Summarize &amp; suggest reply</Button>
                <div v-if="ai" class="mt-3 space-y-3 text-sm">
                    <div v-if="ai.summary">
                        <p class="mb-1 text-xs font-medium text-muted">Summary</p>
                        <p class="text-ink-soft">{{ ai.summary }}</p>
                    </div>
                    <div v-if="ai.replies && ai.replies.length">
                        <p class="mb-1 text-xs font-medium text-muted">Suggested replies</p>
                        <button v-for="(r, i) in ai.replies" :key="i" class="mb-1.5 block w-full rounded-lg bg-sunken p-2 text-left text-xs text-ink-soft hover:bg-hairline" @click="form.body = r">{{ r }}</button>
                    </div>
                    <p v-if="!ai.summary && !(ai.replies && ai.replies.length)" class="text-xs text-muted">AI is off for this workspace, or no suggestion available.</p>
                </div>
            </Card>
        </div>
    </div>
</template>
