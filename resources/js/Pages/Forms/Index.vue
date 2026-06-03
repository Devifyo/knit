<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
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

// Stable keys so vuedraggable can track rows across reordering.
let fieldKey = 0;
const newField = (f = {}) => ({ label: '', type: 'text', required: false, ...f, _k: fieldKey++ });

const open = ref(false);
const editingId = ref(null);
// Name + Email are always included server-side; `fields` are the extra inputs.
const form = useForm({ name: '', nurture_workflow_id: '', fields: [] });

const isEditing = computed(() => editingId.value !== null);

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    // Sensible starting point — fully editable/removable.
    form.fields = [
        newField({ label: 'Name', type: 'text', required: true }),
        newField({ label: 'Email', type: 'email', required: true }),
        newField({ label: 'Phone', type: 'tel', required: false }),
    ];
    open.value = true;
};

const openEdit = (f) => {
    editingId.value = f.id;
    form.clearErrors();
    form.name = f.name;
    form.nurture_workflow_id = f.nurture_workflow_id ?? '';
    form.fields = (f.schema || []).map((x) => newField({ label: x.label, type: x.type, required: !!x.required }));
    open.value = true;
};

const addField = () => form.fields.push(newField());
const removeField = (i) => form.fields.splice(i, 1);

const submit = () => {
    form.transform((data) => ({
        ...data,
        fields: data.fields
            .filter((f) => f.label.trim() !== '')
            .map((f) => ({ label: f.label, type: f.type, required: !!f.required })),
    }));
    const opts = { preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); editingId.value = null; } };
    isEditing.value ? form.put(`/forms/${editingId.value}`, opts) : form.post('/forms', opts);
};

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
            <Button @click="openCreate">New form</Button>
        </div>

        <div v-if="forms.length" class="grid gap-4 sm:grid-cols-2">
            <Card v-for="f in forms" :key="f.id">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-semibold text-ink">{{ f.name }}</h3>
                        <a :href="f.url" target="_blank" class="truncate font-mono text-xs text-[var(--brand)] hover:underline">{{ f.url }}</a>
                    </div>
                    <div class="flex shrink-0 gap-1.5">
                        <button class="rounded-md px-2 py-1 text-xs text-ink-soft ring-1 ring-hairline hover:bg-sunken" @click="openEdit(f)">Edit</button>
                        <button class="rounded-md px-2 py-1 text-xs text-ink-soft ring-1 ring-hairline hover:bg-sunken" @click="copy(f.url)">Copy</button>
                    </div>
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
                <template #action><Button @click="openCreate">Create form</Button></template>
            </EmptyState>
        </Card>

        <Modal :open="open" :title="isEditing ? 'Edit form' : 'New capture form'" @close="open = false">
            <form class="space-y-5" @submit.prevent="submit">
                <Input v-model="form.name" label="Form name" :error="form.errors.name" />

                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label class="block text-xs font-medium text-muted">Fields <span class="text-faint">— drag to reorder</span></label>
                        <button type="button" class="text-xs font-medium text-[var(--brand)] hover:underline" @click="addField">+ Add field</button>
                    </div>
                    <p v-if="form.errors.fields" class="mb-2 text-xs text-critical">{{ form.errors.fields }}</p>

                    <!-- Fully customizable fields (draggable to reorder) -->
                    <draggable v-if="form.fields.length" v-model="form.fields" item-key="_k" handle=".field-grip" ghost-class="opacity-40" class="space-y-2">
                        <template #item="{ element: f, index: i }">
                            <div class="flex items-center gap-2">
                                <button type="button" class="field-grip grid size-8 shrink-0 cursor-grab place-items-center rounded-[var(--radius-control)] text-faint hover:bg-sunken active:cursor-grabbing" title="Drag to reorder">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="size-4"><circle cx="9" cy="6" r="1.4" /><circle cx="9" cy="12" r="1.4" /><circle cx="9" cy="18" r="1.4" /><circle cx="15" cy="6" r="1.4" /><circle cx="15" cy="12" r="1.4" /><circle cx="15" cy="18" r="1.4" /></svg>
                                </button>
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
                        </template>
                    </draggable>
                    <p v-else class="text-xs text-faint">No fields yet — add at least one. Tip: include an Email field so leads can be de-duplicated and scored.</p>
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
                <Button :loading="form.processing" @click="submit">{{ isEditing ? 'Save changes' : 'Create form' }}</Button>
            </template>
        </Modal>
    </div>
</template>
