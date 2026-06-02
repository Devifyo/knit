<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Card, Tag, EmptyState } from '@/Components/ui';

defineProps({ workflow: Object, runs: Array });

const runColor = (s) => ({ completed: 'positive', running: 'info', waiting: 'warning', stopped: 'neutral', failed: 'critical' }[s] ?? 'neutral');
const stepColor = (s) => ({ done: 'positive', skipped: 'neutral', failed: 'critical', pending: 'neutral' }[s] ?? 'neutral');
</script>

<template>
    <Head :title="`${workflow.name} — runs`" />
    <div class="space-y-5">
        <div>
            <Link href="/workflows" class="text-sm text-muted hover:text-ink-soft">← Workflows</Link>
            <h1 class="mt-1 text-xl font-semibold tracking-[-0.02em] text-ink">{{ workflow.name }} — run history</h1>
            <p class="mt-1 font-mono text-xs text-muted">on {{ workflow.trigger_event }}</p>
        </div>

        <div v-if="runs.length" class="space-y-3">
            <Card v-for="run in runs" :key="run.id">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-ink">{{ run.subject }}</span>
                        <Tag size="sm" :color="runColor(run.status)" dot>{{ run.status }}</Tag>
                    </div>
                    <span class="text-xs text-faint">{{ run.started }}</span>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-1.5">
                    <template v-for="(s, i) in run.steps" :key="i">
                        <Tag size="sm" :color="stepColor(s.status)">{{ s.type }} · {{ s.status }}</Tag>
                        <span v-if="i < run.steps.length - 1" class="text-faint">→</span>
                    </template>
                </div>
            </Card>
        </div>
        <Card v-else flush>
            <EmptyState title="No runs yet" description="Trigger this workflow (create a matching record) or hit Test run to see step-by-step execution here." />
        </Card>
    </div>
</template>
