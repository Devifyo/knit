<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Card, Tag } from '@/Components/ui';

defineOptions({ layout: PortalLayout });
defineProps({ contact: Object, stats: Object, recentTickets: Array, deals: Array });

const statusColor = (s) => ({ open: 'info', pending: 'warning', resolved: 'positive', closed: 'neutral' }[s] || 'neutral');
</script>

<template>
    <Head title="Portal" />
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Hi {{ contact.name }}</h1>
            <p class="mt-1 text-sm text-muted">Here's everything we're working on together<span v-if="contact.company"> at {{ contact.company }}</span>.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <Card>
                <p class="text-xs font-medium text-muted">Open tickets</p>
                <p class="mt-1.5 text-2xl font-semibold text-ink nums">{{ stats.open_tickets }}</p>
            </Card>
            <Card>
                <p class="text-xs font-medium text-muted">Total tickets</p>
                <p class="mt-1.5 text-2xl font-semibold text-ink nums">{{ stats.tickets }}</p>
            </Card>
            <Card>
                <p class="text-xs font-medium text-muted">Deals</p>
                <p class="mt-1.5 text-2xl font-semibold text-ink nums">{{ stats.deals }}</p>
            </Card>
        </div>

        <Card title="Recent tickets">
            <template #actions><Link href="/portal/tickets" class="text-xs font-medium text-[var(--brand)] hover:underline">View all →</Link></template>
            <ul v-if="recentTickets.length" class="divide-y divide-hairline-soft">
                <li v-for="t in recentTickets" :key="t.id" class="flex items-center justify-between gap-3 py-2.5">
                    <Link :href="`/portal/tickets/${t.id}`" class="min-w-0">
                        <p class="truncate text-sm font-medium text-ink hover:text-[var(--brand)]">{{ t.subject }}</p>
                        <p class="font-mono text-xs text-faint">{{ t.number }} · {{ t.updated_at }}</p>
                    </Link>
                    <Tag size="sm" :color="statusColor(t.status)">{{ t.status }}</Tag>
                </li>
            </ul>
            <p v-else class="py-3 text-sm text-muted">No tickets yet. <Link href="/portal/tickets" class="text-[var(--brand)] hover:underline">Open one →</Link></p>
        </Card>

        <Card v-if="deals.length" title="Your deals">
            <ul class="divide-y divide-hairline-soft">
                <li v-for="d in deals" :key="d.id" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                    <span class="truncate text-ink">{{ d.name }}</span>
                    <span class="flex items-center gap-2">
                        <span class="nums text-ink">{{ d.amount }}</span>
                        <Tag size="sm" :color="d.status === 'won' ? 'positive' : d.status === 'lost' ? 'critical' : 'neutral'">{{ d.status }}</Tag>
                    </span>
                </li>
            </ul>
        </Card>
    </div>
</template>
