<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Card, Tag } from '@/Components/ui';

defineOptions({ layout: PortalLayout });
defineProps({ deal: Object });

const statusColor = (s) => s === 'won' ? 'positive' : s === 'lost' ? 'critical' : 'neutral';
const quoteColor = (s) => ({ accepted: 'positive', declined: 'critical', sent: 'info' }[s] || 'neutral');
</script>

<template>
    <Head :title="deal.name" />
    <div class="mx-auto max-w-2xl space-y-5">
        <Link href="/portal/deals" class="text-xs text-muted hover:text-ink">← All deals</Link>

        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-[-0.02em] text-ink">{{ deal.name }}</h1>
                <p class="text-sm text-muted"><span v-if="deal.stage">{{ deal.stage }} · </span><span class="nums">{{ deal.amount }}</span></p>
            </div>
            <Tag size="sm" :color="statusColor(deal.status)">{{ deal.status }}</Tag>
        </div>

        <Card v-if="deal.products.length" title="Line items">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-muted"><th class="pb-2 font-medium">Item</th><th class="pb-2 text-right font-medium">Qty</th><th class="pb-2 text-right font-medium">Unit</th></tr></thead>
                <tbody>
                    <tr v-for="(p, i) in deal.products" :key="i" class="border-t border-hairline-soft">
                        <td class="py-2 text-ink">{{ p.name }}<span v-if="p.discount_pct" class="text-xs text-faint"> · {{ p.discount_pct }}% off</span></td>
                        <td class="py-2 text-right nums text-ink-soft">{{ p.quantity }}</td>
                        <td class="py-2 text-right nums text-ink-soft">{{ p.unit_price }}</td>
                    </tr>
                </tbody>
            </table>
        </Card>

        <Card v-if="deal.quotes.length" title="Quotes">
            <ul class="divide-y divide-hairline-soft">
                <li v-for="q in deal.quotes" :key="q.id" class="flex items-center justify-between gap-3 py-2.5">
                    <Link :href="`/portal/quotes/${q.id}`" class="font-mono text-sm text-[var(--brand)] hover:underline">{{ q.number }}</Link>
                    <span class="flex items-center gap-2">
                        <span class="nums text-sm text-ink">{{ q.total }}</span>
                        <Tag size="sm" :color="quoteColor(q.status)">{{ q.status }}</Tag>
                    </span>
                </li>
            </ul>
        </Card>
    </div>
</template>
