<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Card, Button, Input, Tag, EmptyState, Modal } from '@/Components/ui';

defineProps({ forms: Array, workflows: Array });

const fieldTypes = [
    { value: 'text', label: 'Text' },
    { value: 'email', label: 'Email' },
    { value: 'tel', label: 'Phone' },
    { value: 'number', label: 'Number' },
    { value: 'date', label: 'Date' },
    { value: 'datetime', label: 'Date & time' },
];

const open = ref(false);
// Name + Email are always included server-side; these are the extra fields.
const form = useForm({ name: '', nurture_workflow_id: '', fields: [{ label: 'Phone', type: 'tel', required: false }] });
const addField = () => form.fields.push({ label: '', type: 'text', required: false });
const removeField = (i) => form.fields.splice(i, 1);

const submit = () => form
    .transform((data) => ({ ...data, fields: data.fields.filter((f) => f.label.trim() !== '') }))
    .post('/forms', { preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); } });

const copy = (url) => navigator.clipboard?.writeText(url);
const selStyle = 'h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';
</script>

<template>
    <Head title="Forms" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Lead capture forms</h1>
                <p class="mt-1 text-sm text-muted">Submissions create a linked lead and enrol it into a nurture sequence</p>
            </div>
            <Button @click="open = true">New form</Button>
        </div>

        <div v-if="forms.length" class="grid gap-4 sm:grid-cols-2">
            <Card v-for="f in forms" :key="f.id">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-semibold text-ink">{{ f.name }}</h3>
                        <a :href="f.url" target="_blank" class="truncate font-mono text-xs text-[var(--brand)] hover:underline">{{ f.url }}</a>
                    </div>
                    <button class="shrink-0 rounded-md px-2 py-1 text-xs text-ink-soft ring-1 ring-hairline hover:bg-sunken" @click="copy(f.url)">Copy</button>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-muted">
                    <Tag size="sm">{{ f.fields }} fields</Tag>
                    <Tag size="sm" color="brand">{{ f.submissions }} submissions</Tag>
                    <Tag v-if="f.nurture" size="sm" color="positive" dot>nurture: {{ f.nurture }}</Tag>
                    <span v-else class="text-faint">no nurture sequence</span>
                </div>
            </Card>
        </div>
        <Card v-else flush>
            <EmptyState title="No forms yet" description="Create a form, embed its public URL, and route submissions into a nurture sequence.">
                <template #action><Button @click="open = true">Create form</Button></template>
            </EmptyState>
        </Card>

        <Modal :open="open" title="New capture form" @close="open = false">
            <form class="space-y-5" @submit.prevent="submit">
                <Input v-model="form.name" label="Form name" :error="form.errors.name" />

                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label class="block text-xs font-medium text-muted">Fields</label>
                        <button type="button" class="text-xs font-medium text-[var(--brand)] hover:underline" @click="addField">+ Add field</button>
                    </div>

                    <!-- Always-included fields -->
                    <div class="mb-2 flex flex-wrap gap-1.5">
                        <Tag size="sm">Name · required</Tag>
                        <Tag size="sm">Email · required</Tag>
                    </div>

                    <!-- Custom fields -->
                    <div v-if="form.fields.length" class="space-y-2">
                        <div v-for="(f, i) in form.fields" :key="i" class="flex items-center gap-2">
                            <input v-model="f.label" placeholder="Field label" :class="selStyle" class="flex-1" />
                            <select v-model="f.type" :class="selStyle" class="!w-32">
                                <option v-for="t in fieldTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                            <label class="flex shrink-0 items-center gap-1.5 text-xs text-muted">
                                <input v-model="f.required" type="checkbox" class="rounded border-hairline text-[var(--brand)] focus:ring-[var(--brand)]" /> Req.
                            </label>
                            <button type="button" class="grid size-8 shrink-0 place-items-center rounded-[var(--radius-control)] text-faint hover:bg-sunken hover:text-critical" title="Remove field" @click="removeField(i)">
                                <svg viewBox="0 0 24 24" fill="none" class="size-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                    <p v-else class="text-xs text-faint">No extra fields — only Name and Email will be collected.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Nurture sequence (optional)</label>
                    <select v-model="form.nurture_workflow_id" :class="selStyle">
                        <option value="">— none —</option>
                        <option v-for="w in workflows" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                </div>
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">Create form</Button>
            </template>
        </Modal>
    </div>
</template>
