<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Card, Button, Tag } from '@/Components/ui';

defineOptions({ layout: PortalLayout });
const props = defineProps({ ticket: Object });

const statusColor = (s) => ({ open: 'info', pending: 'warning', resolved: 'positive', closed: 'neutral' }[s] || 'neutral');
const selStyle = 'h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';

const form = useForm({ body: '' });
const submit = () => form.post(`/portal/tickets/${props.ticket.id}/reply`, { preserveScroll: true, onSuccess: () => form.reset() });
</script>

<template>
    <Head :title="ticket.subject" />
    <div class="mx-auto max-w-2xl space-y-5">
        <Link href="/portal/tickets" class="text-xs text-muted hover:text-ink">← All tickets</Link>

        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-[-0.02em] text-ink">{{ ticket.subject }}</h1>
                <p class="font-mono text-xs text-faint">{{ ticket.number }} · opened {{ ticket.created_at }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <Tag size="sm">{{ ticket.priority }}</Tag>
                <Tag size="sm" :color="statusColor(ticket.status)">{{ ticket.status }}</Tag>
            </div>
        </div>

        <div class="space-y-3">
            <div v-for="r in ticket.replies" :key="r.id" :class="['rounded-[var(--radius-card)] border p-3.5', r.is_agent ? 'border-hairline bg-surface' : 'brand-wash border-transparent']">
                <div class="mb-1 flex items-center justify-between">
                    <span class="text-xs font-semibold text-ink-soft">{{ r.from }}</span>
                    <span class="text-xs text-faint">{{ r.at }}</span>
                </div>
                <p class="whitespace-pre-line text-sm text-ink">{{ r.body }}</p>
            </div>
        </div>

        <Card title="Reply">
            <form class="space-y-3" @submit.prevent="submit">
                <textarea v-model="form.body" rows="4" :class="selStyle" class="!h-auto py-2" placeholder="Write a reply…"></textarea>
                <p v-if="form.errors.body" class="text-xs text-critical">{{ form.errors.body }}</p>
                <Button type="submit" :loading="form.processing" @click="submit">Send reply</Button>
            </form>
        </Card>
    </div>
</template>
