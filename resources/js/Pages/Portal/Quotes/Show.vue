<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Card, Button, Tag } from '@/Components/ui';

defineOptions({ layout: PortalLayout });
const props = defineProps({ quote: Object, totals: Object });

const quoteColor = (s) => ({ accepted: 'positive', declined: 'critical', sent: 'info' }[s] || 'neutral');
const respond = (decision) => {
    if (decision === 'decline' && ! confirm('Decline this quote?')) return;
    router.post(`/portal/quotes/${props.quote.id}/respond`, { decision }, { preserveScroll: true });
};
</script>

<template>
    <Head :title="quote.number" />
    <div class="mx-auto max-w-2xl space-y-5">
        <Link href="/portal/quotes" class="text-xs text-muted hover:text-ink">← All quotes</Link>

        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="font-mono text-lg font-semibold tracking-[-0.02em] text-ink">{{ quote.number }}</h1>
                <Tag size="sm" :color="quoteColor(quote.status)" class="mt-1">{{ quote.status }}</Tag>
            </div>
            <Button variant="secondary" size="sm" :href="`/portal/quotes/${quote.id}/pdf`">Download PDF</Button>
        </div>

        <Card flush>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-muted"><th class="px-4 py-2 font-medium">Item</th><th class="px-4 py-2 text-right font-medium">Qty</th><th class="px-4 py-2 text-right font-medium">Unit</th><th class="px-4 py-2 text-right font-medium">Amount</th></tr></thead>
                <tbody>
                    <tr v-for="(it, i) in quote.items" :key="i" class="border-t border-hairline-soft">
                        <td class="px-4 py-2.5 text-ink">{{ it.name }}<span v-if="it.discount_pct" class="text-xs text-faint"> · {{ it.discount_pct }}% off</span></td>
                        <td class="px-4 py-2.5 text-right nums text-ink-soft">{{ it.quantity }}</td>
                        <td class="px-4 py-2.5 text-right nums text-ink-soft">{{ it.unit_price }}</td>
                        <td class="px-4 py-2.5 text-right nums text-ink">{{ it.line_total }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="space-y-1 border-t border-hairline px-4 py-3 text-sm">
                <div class="flex justify-between text-muted"><span>Subtotal</span><span class="nums">{{ totals.subtotal_formatted }}</span></div>
                <div v-if="quote.tax_rate" class="flex justify-between text-muted"><span>Tax ({{ quote.tax_rate }}%)</span><span class="nums">{{ totals.tax_formatted }}</span></div>
                <div class="flex justify-between font-semibold text-ink"><span>Total</span><span class="nums">{{ totals.total_formatted }}</span></div>
            </div>
        </Card>

        <p v-if="quote.notes" class="whitespace-pre-line text-sm text-muted">{{ quote.notes }}</p>

        <Card v-if="quote.can_respond" title="Respond to this quote">
            <p class="text-sm text-muted">Let us know if you'd like to go ahead.</p>
            <div class="mt-3 flex gap-2">
                <Button @click="respond('accept')">Accept quote</Button>
                <Button variant="ghost" class="text-critical" @click="respond('decline')">Decline</Button>
            </div>
        </Card>
        <Card v-else>
            <p class="text-sm text-muted">You've <span class="font-medium text-ink">{{ quote.status }}</span> this quote.</p>
        </Card>
    </div>
</template>
