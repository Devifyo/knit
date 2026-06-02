<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, Tag, Button } from '@/Components/ui';

const props = defineProps({ campaign: Object, stats: Object, variants: Array });

const send = () => router.post(`/campaigns/${props.campaign.id}/send`, {}, { preserveScroll: true });
const statusColor = (s) => ({ draft: 'neutral', sending: 'warning', sent: 'positive' }[s] ?? 'neutral');

const cards = (s) => [
    { label: 'Sent', value: s.sent },
    { label: 'Opens', value: `${s.opens} (${s.open_rate}%)` },
    { label: 'Clicks', value: `${s.clicks} (${s.click_rate}%)` },
];
</script>

<template>
    <Head :title="campaign.name" />
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <Link href="/campaigns" class="text-sm text-muted hover:text-ink-soft">← Campaigns</Link>
                <div class="mt-1 flex items-center gap-2">
                    <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">{{ campaign.name }}</h1>
                    <Tag size="sm" :color="statusColor(campaign.status)">{{ campaign.status }}</Tag>
                    <Tag v-if="campaign.ab" size="sm" color="brand">A/B</Tag>
                </div>
                <p class="mt-1 text-sm text-muted">{{ campaign.subject }} · to all {{ campaign.audience }}</p>
            </div>
            <Button v-if="campaign.status !== 'sent'" @click="send">Send campaign</Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <Card v-for="c in cards(stats)" :key="c.label">
                <p class="text-xs font-medium text-muted">{{ c.label }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.02em] text-ink nums">{{ c.value }}</p>
            </Card>
        </div>

        <Card v-if="variants.length" title="A/B variants" subtitle="Open rate by subject line">
            <ul class="divide-y divide-hairline-soft">
                <li v-for="v in variants" :key="v.variant" class="flex items-center justify-between py-3">
                    <div>
                        <Tag size="sm" color="brand">{{ v.variant }}</Tag>
                        <span class="ml-2 text-sm text-ink">{{ v.subject }}</span>
                    </div>
                    <span class="text-sm text-muted nums">{{ v.opens }}/{{ v.sent }} opens</span>
                </li>
            </ul>
        </Card>

        <Card v-if="campaign.status === 'sent'" title="How tracking works">
            <p class="text-sm text-muted">Each recipient gets a unique 1×1 open pixel and a click-wrapped CTA. Opens/clicks above update as recipients engage (open the emails in MailHog/log to see the pixel + redirect fire).</p>
        </Card>
    </div>
</template>
