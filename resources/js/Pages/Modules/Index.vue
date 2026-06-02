<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button, Card, Input, Tag, Modal, EmptyState } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

const props = defineProps({ module: Object, entity: Object, records: Array, contacts: Array, can_manage: Boolean });

const { can } = usePermissions();
const manage = props.can_manage && can('modules.use');

const blank = () => {
    const init = { contact_id: '' };
    props.entity.fields.forEach((f) => { init[f.key] = ''; });
    return init;
};
const open = ref(false);
const form = useForm(blank());
const submit = () => form.post(`/m/${props.module.key}/${props.entity.key}`, {
    preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); },
});
const remove = (id) => {
    if (confirm('Delete this record?')) router.delete(`/m/${props.module.key}/${props.entity.key}/${id}`, { preserveScroll: true });
};

const selStyle = 'h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';
const statusField = computed(() => props.entity.status_field);
</script>

<template>
    <Head :title="entity.label" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">{{ entity.label }}</h1>
                    <Tag v-if="module.hipaa" size="sm" color="warning">HIPAA</Tag>
                </div>
                <p class="mt-1 text-sm text-muted">{{ module.name }} module</p>
            </div>
            <Button v-if="manage" @click="open = true">New {{ entity.singular.toLowerCase() }}</Button>
        </div>

        <Card v-if="records.length" flush>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-hairline text-left text-[11px] uppercase tracking-wider text-faint">
                            <th v-for="f in entity.fields" :key="f.key" class="px-5 py-2.5 font-medium">{{ f.label }}</th>
                            <th v-if="entity.links_contact" class="px-5 py-2.5 font-medium">{{ entity.contact_label || 'Contact' }}</th>
                            <th v-if="manage" class="px-5 py-2.5" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in records" :key="r.id" class="border-b border-hairline-soft last:border-0 hover:bg-sunken/50">
                            <td v-for="f in entity.fields" :key="f.key" class="px-5 py-3 text-ink-soft">
                                <Tag v-if="f.key === statusField && r.values[f.key] !== '—'" size="sm" color="info">{{ r.values[f.key] }}</Tag>
                                <span v-else :class="f.type === 'money' || f.type === 'number' ? 'nums' : ''">{{ r.values[f.key] }}</span>
                            </td>
                            <td v-if="entity.links_contact" class="px-5 py-3">
                                <Link v-if="r.contact_id" :href="`/contacts/${r.contact_id}`" class="text-[var(--brand)] hover:underline">{{ r.contact }}</Link>
                                <span v-else class="text-faint">—</span>
                            </td>
                            <td v-if="manage" class="px-5 py-3 text-right">
                                <button class="text-xs font-medium text-muted hover:text-critical" @click="remove(r.id)">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <EmptyState v-else :title="`No ${entity.label.toLowerCase()} yet`" :description="`Add your first ${entity.singular.toLowerCase()} to get started.`">
            <Button v-if="manage" @click="open = true">New {{ entity.singular.toLowerCase() }}</Button>
        </EmptyState>

        <!-- Create -->
        <Modal :open="open" :title="`New ${entity.singular.toLowerCase()}`" @close="open = false">
            <form class="space-y-4" @submit.prevent="submit">
                <div v-for="f in entity.fields" :key="f.key">
                    <template v-if="f.type === 'select'">
                        <label class="mb-1.5 block text-xs font-medium text-muted">{{ f.label }}</label>
                        <select v-model="form[f.key]" :class="selStyle">
                            <option value="">Select…</option>
                            <option v-for="o in f.options" :key="o" :value="o">{{ o }}</option>
                        </select>
                    </template>
                    <template v-else-if="f.type === 'textarea'">
                        <label class="mb-1.5 block text-xs font-medium text-muted">{{ f.label }}</label>
                        <textarea v-model="form[f.key]" rows="3" class="w-full rounded-[var(--radius-control)] bg-surface px-3 py-2 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]" />
                    </template>
                    <Input
                        v-else
                        v-model="form[f.key]"
                        :label="f.label + (f.type === 'money' ? ' (USD)' : '')"
                        :type="f.type === 'money' || f.type === 'number' ? 'number' : f.type === 'date' ? 'date' : 'text'"
                        :error="form.errors[f.key]"
                    />
                    <p v-if="f.type === 'select' && form.errors[f.key]" class="mt-1 text-xs text-critical">{{ form.errors[f.key] }}</p>
                </div>

                <div v-if="entity.links_contact">
                    <label class="mb-1.5 block text-xs font-medium text-muted">{{ entity.contact_label || 'Contact' }}</label>
                    <select v-model="form.contact_id" :class="selStyle">
                        <option value="">None</option>
                        <option v-for="c in contacts" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2">
                    <Button type="button" variant="secondary" @click="open = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Create</Button>
                </div>
            </form>
        </Modal>
    </div>
</template>
