<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button, Card, Tag, EmptyState, Modal, Input } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

defineProps({ workflows: Array });
const { can } = usePermissions();

const open = ref(false);
const form = useForm({ name: 'New lead follow-up' });
const submit = () => form.post('/workflows', { preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); } });
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
            <Button v-if="can('workflows.manage')" @click="open = true">New workflow</Button>
        </div>

        <div v-if="workflows.length" class="grid gap-4 sm:grid-cols-2">
            <Card v-for="w in workflows" :key="w.id">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-semibold text-ink">{{ w.name }}</h3>
                        <p class="mt-0.5 font-mono text-xs text-muted">on {{ w.trigger_event }}</p>
                    </div>
                    <button
                        v-if="can('workflows.manage')"
                        class="relative h-5 w-9 shrink-0 rounded-full transition-colors"
                        :class="w.enabled ? 'bg-[var(--brand)]' : 'bg-hairline'"
                        @click="toggle(w.id)"
                    >
                        <span class="absolute top-0.5 size-4 rounded-full bg-white shadow transition-all" :class="w.enabled ? 'left-[18px]' : 'left-0.5'" />
                    </button>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-1.5">
                    <template v-for="(s, i) in w.steps" :key="i">
                        <Tag size="sm" color="neutral">{{ stepLabel[s] ?? s }}</Tag>
                        <span v-if="i < w.steps.length - 1" class="text-faint">→</span>
                    </template>
                </div>
                <p class="mt-3 text-xs text-faint">{{ w.runs_count }} {{ w.runs_count === 1 ? 'run' : 'runs' }}</p>
            </Card>
        </div>
        <Card v-else flush>
            <EmptyState title="No workflows yet" description="Create the new-lead follow-up sequence: wait a day, send an email, and if there's no reply, open a task.">
                <template #action><Button v-if="can('workflows.manage')" @click="open = true">Create workflow</Button></template>
            </EmptyState>
        </Card>

        <Modal :open="open" title="New workflow" @close="open = false">
            <p class="mb-4 text-sm text-muted">Creates the follow-up sequence on the <span class="font-mono text-ink-soft">lead.created</span> trigger: wait 1 day → send email → if the lead is still new, create a task.</p>
            <Input v-model="form.name" label="Name" :error="form.errors.name" />
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">Create</Button>
            </template>
        </Modal>
    </div>
</template>
