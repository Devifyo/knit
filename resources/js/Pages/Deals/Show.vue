<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Card, Tag, Button, Input } from '@/Components/ui';

const props = defineProps({ deal: Object, catalog: Array });
const page = usePage();
const ai = computed(() => page.props.flash?.ai);
const insight = () => router.post(`/deals/${props.deal.id}/insight`, {}, { preserveScroll: true });
const riskColor = (r) => ({ low: 'positive', medium: 'warning', high: 'critical' }[r] ?? 'neutral');

const newQuote = useForm({ deal_id: props.deal.id, currency: 'USD', tax_rate: 0 });
const createQuote = () => newQuote.post('/quotes');

const prod = useForm({ product_id: '', quantity: 1, discount_pct: 0 });
const addProduct = () => prod.post(`/deals/${props.deal.id}/products`, { preserveScroll: true, onSuccess: () => prod.reset() });
const removeProduct = (pivotId) => router.delete(`/deals/${props.deal.id}/products/${pivotId}`, { preserveScroll: true });
const selStyle = 'h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';

const statusColor = (s) => ({ open: 'info', won: 'positive', lost: 'critical' }[s] ?? 'neutral');
const qStatusColor = (s) => ({ draft: 'neutral', sent: 'info', accepted: 'positive', declined: 'critical' }[s] ?? 'neutral');
</script>

<template>
    <Head :title="deal.name" />
    <div class="space-y-5">
        <div>
            <Link href="/deals" class="text-sm text-muted hover:text-ink-soft">← Pipeline</Link>
            <div class="mt-1 flex items-center gap-3">
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">{{ deal.name }}</h1>
                <Tag :color="statusColor(deal.status)" size="sm">{{ deal.status }}</Tag>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[320px_1fr]">
            <div class="space-y-5">
            <Card title="AI insight">
                <Button size="sm" variant="secondary" class="w-full" @click="insight">Next action &amp; risk</Button>
                <div v-if="ai" class="mt-3 space-y-2.5 text-sm">
                    <div v-if="ai.next">
                        <p class="text-xs font-medium text-muted">Recommended next action</p>
                        <p class="font-medium text-ink">{{ ai.next.action }}</p>
                        <p class="text-xs text-muted">{{ ai.next.rationale }}</p>
                    </div>
                    <div v-if="ai.risk" class="flex items-center gap-2">
                        <span class="text-xs text-muted">Risk:</span><Tag size="sm" :color="riskColor(ai.risk.risk)">{{ ai.risk.risk }}</Tag>
                    </div>
                </div>
            </Card>

            <Card title="Deal">
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between"><dt class="text-muted">Amount</dt><dd class="nums text-ink">{{ deal.amount }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Stage</dt><dd><Tag size="sm" color="brand">{{ deal.stage }}</Tag></dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Probability</dt><dd class="text-ink-soft">{{ deal.probability ?? '—' }}%</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Close date</dt><dd class="text-ink-soft">{{ deal.expected_close_date ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Contact</dt><dd class="truncate text-ink-soft"><Link v-if="deal.contact_id" :href="`/contacts/${deal.contact_id}`" class="text-[var(--brand)] hover:underline">{{ deal.contact }}</Link><span v-else>—</span></dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Company</dt><dd class="text-ink-soft">{{ deal.company ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Owner</dt><dd class="text-ink-soft">{{ deal.owner ?? '—' }}</dd></div>
                </dl>
            </Card>
            </div>

            <div class="space-y-5">
                <Card title="Products" subtitle="Line items — the deal amount is the sum of these">
                    <ul v-if="deal.products.length" class="mb-4 divide-y divide-hairline-soft">
                        <li v-for="p in deal.products" :key="p.pivot_id" class="flex items-center justify-between py-2.5 text-sm">
                            <div>
                                <span class="font-medium text-ink">{{ p.name }}</span>
                                <span class="text-muted"> × {{ p.quantity }}<span v-if="p.discount_pct"> · -{{ p.discount_pct }}%</span></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="nums text-ink">{{ p.unit_price }}</span>
                                <button class="text-faint hover:text-critical" @click="removeProduct(p.pivot_id)">✕</button>
                            </div>
                        </li>
                    </ul>
                    <p v-else class="mb-4 text-sm text-muted">No products yet — add from the catalog to set the deal amount.</p>
                    <form class="flex flex-wrap items-end gap-2" @submit.prevent="addProduct">
                        <div class="min-w-40 flex-1">
                            <label class="mb-1.5 block text-xs font-medium text-muted">Product</label>
                            <select v-model="prod.product_id" :class="selStyle">
                                <option value="">Select…</option>
                                <option v-for="c in catalog" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <Input v-model="prod.quantity" type="number" label="Qty" class="w-20" />
                        <Input v-model="prod.discount_pct" type="number" label="Disc %" class="w-24" />
                        <Button :loading="prod.processing" @click="addProduct">Add</Button>
                    </form>
                </Card>

                <Card title="Quotes" subtitle="Proposals attached to this deal">
                    <template #header><Button size="sm" :loading="newQuote.processing" @click="createQuote">Create quote</Button></template>
                    <ul v-if="deal.quotes.length" class="divide-y divide-hairline-soft">
                        <li v-for="q in deal.quotes" :key="q.id" class="flex items-center justify-between py-2.5">
                            <Link :href="`/quotes/${q.id}`" class="font-mono text-sm text-[var(--brand)] hover:underline">{{ q.number }}</Link>
                            <div class="flex items-center gap-3">
                                <span class="nums text-sm text-ink">{{ q.total }}</span>
                                <Tag size="sm" :color="qStatusColor(q.status)">{{ q.status }}</Tag>
                            </div>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-muted">No quotes yet — create one to build a proposal and export a PDF.</p>
                </Card>

                <Card title="Activity">
                    <ol v-if="deal.activities.length" class="space-y-3">
                        <li v-for="a in deal.activities" :key="a.id" class="flex items-start gap-2">
                            <Tag size="sm" color="neutral">{{ a.type }}</Tag>
                            <div><p v-if="a.body" class="text-sm text-ink-soft">{{ a.body }}</p><p class="text-xs text-faint">{{ a.author || 'System' }} · {{ a.at }}</p></div>
                        </li>
                    </ol>
                    <p v-else class="text-sm text-muted">No activity yet.</p>
                </Card>
            </div>
        </div>
    </div>
</template>
