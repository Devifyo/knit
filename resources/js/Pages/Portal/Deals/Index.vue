<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Card, Tag, EmptyState } from '@/Components/ui';

defineOptions({ layout: PortalLayout });
defineProps({ deals: Array });

const statusColor = (s) => s === 'won' ? 'positive' : s === 'lost' ? 'critical' : 'neutral';
</script>

<template>
    <Head title="Deals" />
    <div class="space-y-5">
        <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Your deals</h1>

        <Card v-if="deals.length" flush>
            <ul class="divide-y divide-hairline-soft">
                <li v-for="d in deals" :key="d.id">
                    <Link :href="`/portal/deals/${d.id}`" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-sunken">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink">{{ d.name }}</p>
                            <p v-if="d.stage" class="text-xs text-faint">{{ d.stage }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <span class="nums text-sm text-ink">{{ d.amount }}</span>
                            <Tag size="sm" :color="statusColor(d.status)">{{ d.status }}</Tag>
                        </div>
                    </Link>
                </li>
            </ul>
        </Card>
        <Card v-else flush>
            <EmptyState title="No deals yet" description="Deals you're part of will show up here." />
        </Card>
    </div>
</template>
