<script setup>
import { Head } from '@inertiajs/vue3';
import { Card, Avatar, Tag, EmptyState } from '@/Components/ui';

defineProps({ activities: Array });

const tone = (type) => ({ note: 'neutral', call: 'info', email: 'info', meeting: 'brand' }[type] ?? 'neutral');
</script>

<template>
    <Head title="Activity feed" />
    <div class="mx-auto max-w-3xl space-y-5">
        <div>
            <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Activity feed</h1>
            <p class="mt-1 text-sm text-muted">Everything happening across your workspace, newest first</p>
        </div>

        <Card v-if="activities.length" flush>
            <ol class="divide-y divide-hairline-soft">
                <li v-for="a in activities" :key="a.id" class="flex gap-3 px-5 py-3.5">
                    <Avatar :name="a.author" class="mt-0.5 size-7 shrink-0 text-[10px]" />
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-ink">{{ a.author }}</span>
                            <Tag :color="tone(a.type)" size="sm">{{ a.type }}</Tag>
                            <span v-if="a.subject" class="text-xs text-muted">on {{ a.subject }}</span>
                            <span class="ml-auto shrink-0 text-xs text-faint">{{ a.at }}</span>
                        </div>
                        <p v-if="a.body" class="mt-1 line-clamp-3 text-sm text-ink-soft">{{ a.body }}</p>
                    </div>
                </li>
            </ol>
        </Card>

        <EmptyState v-else title="No activity yet" description="Notes, calls, emails and meetings will appear here as your team works." />
    </div>
</template>
