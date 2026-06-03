<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Card, Tag, EmptyState } from '@/Components/ui';

defineOptions({ layout: PortalLayout });
defineProps({ quotes: Array });

const quoteColor = (s) => ({ accepted: 'positive', declined: 'critical', sent: 'info' }[s] || 'neutral');
</script>

<template>
    <Head title="Quotes" />
    <div class="space-y-5">
        <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Your quotes</h1>

        <Card v-if="quotes.length" flush>
            <ul class="divide-y divide-hairline-soft">
                <li v-for="q in quotes" :key="q.id">
                    <Link :href="`/portal/quotes/${q.id}`" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-sunken">
                        <div class="min-w-0">
                            <p class="truncate font-mono text-sm font-medium text-ink">{{ q.number }}</p>
                            <p v-if="q.deal" class="truncate text-xs text-faint">{{ q.deal }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <span class="nums text-sm text-ink">{{ q.total }}</span>
                            <Tag size="sm" :color="quoteColor(q.status)">{{ q.status }}</Tag>
                        </div>
                    </Link>
                </li>
            </ul>
        </Card>
        <Card v-else flush>
            <EmptyState title="No quotes yet" description="Quotes shared with you will appear here." />
        </Card>
    </div>
</template>
