<script setup>
import { reactive } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button, Card, Input } from '@/Components/ui';

const props = defineProps({ workflow: Object, triggers: Array, stepTypes: Array });

const isEdit = !!props.workflow;
const form = useForm({
    name: props.workflow?.name ?? '',
    trigger_event: props.workflow?.trigger_event ?? props.triggers[0]?.value,
    enabled: props.workflow?.enabled ?? true,
    steps: props.workflow?.steps?.map((s) => ({ type: s.type, config: { ...s.config } })) ?? [],
});

const defaults = {
    wait: { days: 1 },
    send_email: { to_field: 'email', subject: '', body: '' },
    create_task: { title: 'Follow up', due_in_days: 1, assign_to_field: 'assigned_user_id' },
    condition: { condition: { operator: 'and', rules: [{ field: 'status', op: 'equals', value: '' }] } },
    add_tag: { tag: '' },
    update_field: { field: '', value: '' },
    assign_owner: { user_id: '' },
    webhook: { url: '' },
};
const selStyle = 'h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';

const addStep = () => form.steps.push({ type: 'send_email', config: { ...defaults.send_email } });
const removeStep = (i) => form.steps.splice(i, 1);
const move = (i, d) => { const j = i + d; if (j < 0 || j >= form.steps.length) return; [form.steps[i], form.steps[j]] = [form.steps[j], form.steps[i]]; };
const onType = (step) => { step.config = JSON.parse(JSON.stringify(defaults[step.type] ?? {})); };

const ops = ['equals', 'not_equals', 'contains', 'gt', 'lt', 'gte', 'lte', 'is_empty', 'is_not_empty'];
const submit = () => isEdit
    ? form.put(`/workflows/${props.workflow.id}`)
    : form.post('/workflows');
</script>

<template>
    <Head :title="isEdit ? 'Edit workflow' : 'New workflow'" />
    <div class="mx-auto max-w-3xl space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <Link href="/workflows" class="text-sm text-muted hover:text-ink-soft">← Workflows</Link>
                <h1 class="mt-1 text-xl font-semibold tracking-[-0.02em] text-ink">{{ isEdit ? 'Edit workflow' : 'New workflow' }}</h1>
            </div>
            <Button :loading="form.processing" @click="submit">{{ isEdit ? 'Save changes' : 'Create workflow' }}</Button>
        </div>

        <Card title="Trigger" subtitle="When should this run?">
            <div class="grid gap-4 sm:grid-cols-2">
                <Input v-model="form.name" label="Workflow name" :error="form.errors.name" />
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Trigger event</label>
                    <select v-model="form.trigger_event" :class="selStyle">
                        <option v-for="t in triggers" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                </div>
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm text-ink-soft">
                <input v-model="form.enabled" type="checkbox" class="rounded border-hairline text-[var(--brand)]" /> Enabled
            </label>
        </Card>

        <div class="space-y-3">
            <div v-for="(step, i) in form.steps" :key="i" class="relative rounded-[var(--radius-card)] border border-hairline bg-surface p-4 shadow-e1">
                <div class="flex items-center gap-3">
                    <span class="grid size-6 shrink-0 place-items-center rounded-full brand-wash text-xs font-semibold text-[var(--brand)]">{{ i + 1 }}</span>
                    <select v-model="step.type" :class="selStyle" class="!w-56" @change="onType(step)">
                        <option v-for="t in stepTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                    <div class="ml-auto flex items-center gap-1 text-muted">
                        <button class="rounded p-1 hover:bg-sunken" :disabled="i === 0" @click="move(i, -1)">↑</button>
                        <button class="rounded p-1 hover:bg-sunken" :disabled="i === form.steps.length - 1" @click="move(i, 1)">↓</button>
                        <button class="rounded p-1 text-critical hover:bg-critical/10" @click="removeStep(i)">✕</button>
                    </div>
                </div>

                <div class="mt-3 grid gap-3 pl-9 sm:grid-cols-2">
                    <template v-if="step.type === 'wait'">
                        <Input v-model.number="step.config.days" type="number" label="Wait (days)" />
                        <Input v-model.number="step.config.hours" type="number" label="…plus hours" />
                    </template>
                    <template v-else-if="step.type === 'send_email'">
                        <Input v-model="step.config.subject" label="Subject" class="sm:col-span-2" />
                        <Input v-model="step.config.body" label="Body" class="sm:col-span-2" />
                    </template>
                    <template v-else-if="step.type === 'create_task'">
                        <Input v-model="step.config.title" label="Task title" />
                        <Input v-model.number="step.config.due_in_days" type="number" label="Due in (days)" />
                    </template>
                    <template v-else-if="step.type === 'condition'">
                        <Input v-model="step.config.condition.rules[0].field" label="Field" />
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-muted">Operator</label>
                            <select v-model="step.config.condition.rules[0].op" :class="selStyle">
                                <option v-for="o in ops" :key="o" :value="o">{{ o }}</option>
                            </select>
                        </div>
                        <Input v-model="step.config.condition.rules[0].value" label="Value" />
                        <p class="text-xs text-muted sm:col-span-2">If false, the workflow stops here.</p>
                    </template>
                    <template v-else-if="step.type === 'add_tag'">
                        <Input v-model="step.config.tag" label="Tag" />
                    </template>
                    <template v-else-if="step.type === 'update_field'">
                        <Input v-model="step.config.field" label="Field" />
                        <Input v-model="step.config.value" label="Value" />
                    </template>
                    <template v-else-if="step.type === 'assign_owner'">
                        <Input v-model.number="step.config.user_id" type="number" label="User ID" />
                    </template>
                    <template v-else-if="step.type === 'webhook'">
                        <Input v-model="step.config.url" label="Webhook URL" class="sm:col-span-2" />
                    </template>
                </div>
            </div>

            <button class="flex w-full items-center justify-center gap-2 rounded-[var(--radius-card)] border border-dashed border-hairline py-3 text-sm text-muted transition-colors hover:border-faint hover:text-ink-soft" @click="addStep">
                + Add step
            </button>
            <p v-if="form.errors.steps" class="text-xs text-critical">{{ form.errors.steps }}</p>
        </div>
    </div>
</template>
