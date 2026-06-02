<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Button, Card, Tag, EmptyState } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

defineProps({ workflows: Array });
const { can } = usePermissions();

const toggle = (id) => router.patch(`/workflows/${id}/toggle`, {}, { preserveScroll: true });

const stepLabel = { wait: 'Wait', send_email: 'Email', create_task: 'Task', condition: 'If/else', update_field: 'Update', add_tag: 'Tag', assign_owner: 'Assign', webhook: 'Webhook' };
</script>

<template>
    <Head title="Workflows" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Workflows</h1>
                <p class="mt-1 text-sm text-muted">Automate follow-ups, tasks and notifications</p>
            </div>
            <Button v-if="can('workflows.manage')" href="/workflows/create">New workflow</Button>
        </div>

        <div v-if="workflows.length" class="grid gap-4 sm:grid-cols-2">
            <Card v-for="w in workflows" :key="w.id">
                <div class="flex items-start justify-between gap-3">
                    <Link :href="`/workflows/${w.id}/edit`" class="min-w-0 flex-1">
                        <h3 class="truncate text-sm font-semibold text-ink hover:text-[var(--brand)]">{{ w.name }}</h3>
                        <p class="mt-0.5 font-mono text-xs text-muted">on {{ w.trigger_event }}</p>
                    </Link>
                    <button
                        v-if="can('workflows.manage')"
                        class="relative h-5 w-9 shrink-0 rounded-full transition-colors"
                        :class="w.enabled ? 'bg-[var(--brand)]' : 'bg-hairline'"
                        @click="toggle(w.id)"
                    >
                        <span class="absolute top-0.5 size-4 rounded-full bg-white shadow transition-all" :class="w.enabled ? 'left-[18px]' : 'left-0.5'" />
                    </button>
                </div>
                <Link :href="`/workflows/${w.id}/edit`" class="mt-3 flex flex-wrap items-center gap-1.5">
                    <template v-for="(s, i) in w.steps" :key="i">
                        <Tag size="sm" color="neutral">{{ stepLabel[s] ?? s }}</Tag>
                        <span v-if="i < w.steps.length - 1" class="text-faint">→</span>
                    </template>
                    <span v-if="!w.steps.length" class="text-xs text-faint">No steps yet — click to build</span>
                </Link>
                <p class="mt-3 text-xs text-faint">{{ w.runs_count }} {{ w.runs_count === 1 ? 'run' : 'runs' }}</p>
            </Card>
        </div>
        <Card v-else flush>
            <EmptyState title="No workflows yet" description="Build a sequence: pick a trigger, then add steps like wait, send email, branch and create task.">
                <template #action><Button v-if="can('workflows.manage')" href="/workflows/create">Create workflow</Button></template>
            </EmptyState>
        </Card>
    </div>
</template>
