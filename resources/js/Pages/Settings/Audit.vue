<script setup>
import { Head } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Card, Tag } from '@/Components/ui';

defineOptions({ layout: SettingsLayout });
defineProps({ audits: Array });

const eventColor = (e) => ({ created: 'positive', updated: 'info', deleted: 'critical', restored: 'warning' }[e] ?? 'neutral');
</script>

<template>
    <Head title="Audit log" />
    <div class="space-y-5">
        <div>
            <h2 class="text-sm font-semibold tracking-[-0.01em] text-ink">Audit log</h2>
            <p class="mt-1 text-sm text-muted">Every change to contacts, deals and leads — who, what and when.</p>
        </div>

        <Card flush>
            <ul v-if="audits.length" class="divide-y divide-hairline-soft">
                <li v-for="a in audits" :key="a.id" class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                    <div class="flex items-center gap-3">
                        <Tag :color="eventColor(a.event)" size="sm">{{ a.event }}</Tag>
                        <span class="text-ink">{{ a.model }} #{{ a.model_id }}</span>
                        <span v-if="a.changes.length" class="hidden text-xs text-muted sm:inline">{{ a.changes.join(', ') }}</span>
                    </div>
                    <div class="flex shrink-0 items-center gap-3 text-xs text-muted">
                        <span>{{ a.user }}</span>
                        <span class="nums">{{ a.ip }}</span>
                        <span>{{ a.at }}</span>
                    </div>
                </li>
            </ul>
            <p v-else class="px-5 py-6 text-sm text-muted">No audit entries yet.</p>
        </Card>
    </div>
</template>
