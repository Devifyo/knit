<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Button, Card, Input, Tag } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

defineOptions({ layout: SettingsLayout });
const props = defineProps({ subscription: Object, usage: Object, plans: Array, invoices: Array });

const { can } = usePermissions();
const manage = can('billing.manage');

const coupon = ref('');
const subscribe = (planKey) => useForm({ plan: planKey, coupon: coupon.value }).post('/settings/billing/subscribe', { preserveScroll: true });
const cancel = () => useForm({}).post('/settings/billing/cancel', { preserveScroll: true });

const statusColor = (s) => ({ active: 'positive', trialing: 'info', past_due: 'warning', canceled: 'critical' }[s] ?? 'neutral');
const invColor = (s) => ({ paid: 'positive', open: 'warning', void: 'neutral' }[s] ?? 'neutral');
const seatLabel = (limit) => limit == null ? 'Unlimited' : limit;
</script>

<template>
    <Head title="Billing" />
    <div class="space-y-6">
        <!-- Current plan + usage -->
        <Card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-medium text-muted">Current plan</p>
                    <div class="mt-1 flex items-center gap-2">
                        <span class="text-lg font-semibold tracking-[-0.01em] text-ink">{{ usage.plan }}</span>
                        <Tag v-if="subscription" :color="statusColor(subscription.status)" size="sm">{{ subscription.status }}</Tag>
                    </div>
                    <p v-if="subscription?.on_trial" class="mt-1 text-xs text-muted">Trial ends {{ subscription.trial_ends_at }}</p>
                    <p v-else-if="subscription?.renews_at && !subscription.canceled" class="mt-1 text-xs text-muted">Renews {{ subscription.renews_at }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-medium text-muted">Seats</p>
                    <p class="mt-1 text-lg font-semibold tracking-[-0.01em] text-ink nums">{{ usage.seats_used }} / {{ seatLabel(usage.seat_limit) }}</p>
                </div>
            </div>
            <div v-if="manage && subscription && !subscription.canceled && subscription.status !== 'canceled'" class="mt-4 border-t border-hairline-soft pt-3">
                <Button variant="ghost" class="text-critical" @click="cancel">Cancel subscription</Button>
            </div>
        </Card>

        <!-- Plans -->
        <div>
            <div class="mb-3 flex items-end justify-between gap-3">
                <h2 class="text-sm font-semibold tracking-[-0.01em] text-ink">Plans</h2>
                <div v-if="manage" class="w-56">
                    <Input v-model="coupon" placeholder="Coupon code (optional)" class="h-9" />
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="p in plans" :key="p.key" :class="p.current ? 'ring-2 ring-[var(--brand)]' : ''">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[15px] font-semibold tracking-[-0.01em] text-ink">{{ p.name }}</h3>
                        <Tag v-if="p.current" color="brand" size="sm">Current</Tag>
                    </div>
                    <p class="mt-2"><span class="text-2xl font-semibold tracking-[-0.02em] text-ink nums">{{ p.price }}</span><span class="text-sm text-muted">/{{ p.interval }}</span></p>
                    <ul class="mt-3 space-y-1.5 text-sm text-ink-soft">
                        <li>{{ p.seats == null ? 'Unlimited' : p.seats }} seats</li>
                        <li v-for="(val, key) in p.features" :key="key" class="capitalize">{{ key.replace('_', ' ') }}: {{ val === true ? 'Yes' : val }}</li>
                    </ul>
                    <Button v-if="manage && !p.current" class="mt-4 w-full" variant="secondary" @click="subscribe(p.key)">
                        {{ p.price_minor === 0 ? 'Switch to' : 'Choose' }} {{ p.name }}
                    </Button>
                </Card>
            </div>
        </div>

        <!-- Invoices -->
        <Card title="Invoices" subtitle="Download as PDF">
            <ul v-if="invoices.length" class="divide-y divide-hairline-soft">
                <li v-for="i in invoices" :key="i.id" class="flex items-center justify-between py-2.5 text-sm">
                    <div class="flex items-center gap-3">
                        <a :href="`/settings/billing/invoices/${i.id}/pdf`" class="font-mono text-[var(--brand)] hover:underline">{{ i.number }}</a>
                        <Tag :color="invColor(i.status)" size="sm">{{ i.status }}</Tag>
                    </div>
                    <div class="flex items-center gap-4 text-muted">
                        <span>{{ i.issued_at }}</span>
                        <span class="nums font-medium text-ink">{{ i.total }}</span>
                    </div>
                </li>
            </ul>
            <p v-else class="text-sm text-muted">No invoices yet.</p>
        </Card>
    </div>
</template>
