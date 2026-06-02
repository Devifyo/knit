<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Button, Card, Input, DataTable, Tag, Modal, Avatar } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

const props = defineProps({ contacts: Array, search: String, customFields: Array, companies: Array });
const { can } = usePermissions();

const search = ref(props.search ?? '');
watchDebounced(search, (v) => router.get('/contacts', { search: v }, { preserveState: true, replace: true, preserveScroll: true }), { debounce: 300 });

const open = ref(false);
const form = useForm({ first_name: '', last_name: '', email: '', phone: '', job_title: '', company_id: '', custom_fields: {} });
const submit = () => form.post('/contacts', { preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); } });

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email' },
    { key: 'company', label: 'Company' },
    { key: 'lifecycle_stage', label: 'Stage' },
];
const stageColor = (s) => ({ customer: 'positive', sql: 'info', mql: 'brand', lead: 'neutral' }[s] ?? 'neutral');
</script>

<template>
    <Head title="Contacts" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Contacts</h1>
                <p class="mt-1 text-sm text-muted">{{ contacts.length }} {{ contacts.length === 1 ? 'person' : 'people' }} in your workspace</p>
            </div>
            <Button v-if="can('contacts.manage')" @click="open = true">New contact</Button>
        </div>

        <div class="max-w-xs">
            <Input v-model="search" placeholder="Search name or email…" />
        </div>

        <DataTable :columns="columns" :rows="contacts" clickable empty-title="No contacts yet" empty-description="Add your first contact to start building relationships." @row-click="(r) => router.visit(`/contacts/${r.id}`)">
            <template #cell:name="{ row }">
                <div class="flex items-center gap-2.5">
                    <Avatar :name="row.name" size="sm" />
                    <div>
                        <p class="font-medium text-ink">{{ row.name }}</p>
                        <p v-if="row.job_title" class="text-xs text-muted">{{ row.job_title }}</p>
                    </div>
                </div>
            </template>
            <template #cell:lifecycle_stage="{ value }"><Tag :color="stageColor(value)" size="sm">{{ value }}</Tag></template>
        </DataTable>

        <Modal :open="open" title="New contact" @close="open = false">
            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid grid-cols-2 gap-3">
                    <Input v-model="form.first_name" label="First name" :error="form.errors.first_name" />
                    <Input v-model="form.last_name" label="Last name" />
                </div>
                <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" />
                <div class="grid grid-cols-2 gap-3">
                    <Input v-model="form.phone" label="Phone" />
                    <Input v-model="form.job_title" label="Job title" />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Company</label>
                    <select v-model="form.company_id" class="h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
                        <option value="">—</option>
                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <!-- Custom fields (defined per tenant in settings) render here automatically. -->
                <div v-for="f in customFields" :key="f.key">
                    <Input v-model="form.custom_fields[f.key]" :label="f.label + ' (custom)'" />
                </div>
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">Create contact</Button>
            </template>
        </Modal>
    </div>
</template>
