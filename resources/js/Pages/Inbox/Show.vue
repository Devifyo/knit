<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Card, Tag, Button, Avatar } from '@/Components/ui';

const props = defineProps({ conversation: Object, members: Array });

const mode = ref('reply');
const form = useForm({ body: '' });
const send = () => {
    const url = mode.value === 'reply' ? `/inbox/${props.conversation.id}/reply` : `/inbox/${props.conversation.id}/note`;
    form.post(url, { preserveScroll: true, onSuccess: () => form.reset() });
};
const assign = (e) => router.patch(`/inbox/${props.conversation.id}/assign`, { assigned_user_id: e.target.value || null }, { preserveScroll: true });
const setStatus = (status) => router.patch(`/inbox/${props.conversation.id}/status`, { status }, { preserveScroll: true });
const selStyle = 'h-8 rounded-[var(--radius-control)] bg-surface px-2 text-xs text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';
</script>

<template>
    <Head :title="conversation.subject" />
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <Link href="/inbox" class="text-sm text-muted hover:text-ink-soft">← Inbox</Link>
                <h1 class="mt-1 text-lg font-semibold tracking-[-0.02em] text-ink">{{ conversation.subject }}</h1>
                <p class="text-sm text-muted">
                    <Link v-if="conversation.contact_id" :href="`/contacts/${conversation.contact_id}`" class="text-[var(--brand)] hover:underline">{{ conversation.contact }}</Link>
                    <span v-else>Unlinked sender</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <select :class="selStyle" :value="''" @change="assign">
                    <option value="">Unassigned</option>
                    <option v-for="m in members" :key="m.id" :value="m.id" :selected="m.name === conversation.assignee">{{ m.name }}</option>
                </select>
                <Button size="sm" variant="secondary" @click="setStatus(conversation.status === 'open' ? 'closed' : 'open')">
                    {{ conversation.status === 'open' ? 'Close' : 'Reopen' }}
                </Button>
            </div>
        </div>

        <Card flush>
            <div class="space-y-4 p-5">
                <div v-for="m in conversation.messages" :key="m.id" class="flex" :class="m.direction === 'outbound' && !m.is_internal ? 'justify-end' : 'justify-start'">
                    <div
                        class="max-w-[80%] rounded-[var(--radius-card)] px-4 py-2.5 text-sm"
                        :class="m.is_internal ? 'border border-warning/30 bg-warning/10 text-ink-soft' : m.direction === 'outbound' ? 'bg-[var(--brand)] text-white' : 'bg-sunken text-ink'"
                    >
                        <div class="mb-1 flex items-center gap-2 text-[11px]" :class="m.direction === 'outbound' && !m.is_internal ? 'text-white/70' : 'text-muted'">
                            <span class="font-medium">{{ m.from }}</span>
                            <Tag v-if="m.is_internal" size="sm" color="warning">internal note</Tag>
                            <span>· {{ m.at }}</span>
                        </div>
                        <p class="whitespace-pre-wrap">{{ m.body }}</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-hairline-soft p-4">
                <div class="mb-2 flex gap-1">
                    <button :class="['rounded-md px-2.5 py-1 text-xs font-medium', mode === 'reply' ? 'brand-wash text-[var(--brand)]' : 'text-muted hover:bg-sunken']" @click="mode = 'reply'">Reply</button>
                    <button :class="['rounded-md px-2.5 py-1 text-xs font-medium', mode === 'note' ? 'bg-warning/10 text-warning' : 'text-muted hover:bg-sunken']" @click="mode = 'note'">Internal note</button>
                </div>
                <textarea
                    v-model="form.body" rows="3"
                    :placeholder="mode === 'reply' ? 'Type a reply to the customer…' : 'Add an internal note — @mention a teammate'"
                    class="w-full rounded-[var(--radius-control)] bg-surface p-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]"
                />
                <div class="mt-2 flex justify-end">
                    <Button :loading="form.processing" @click="send">{{ mode === 'reply' ? 'Send reply' : 'Add note' }}</Button>
                </div>
            </div>
        </Card>
    </div>
</template>
