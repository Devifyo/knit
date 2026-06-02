<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Button, Input, DataTable, Modal } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

const props = defineProps({ companies: Array, search: String });
const { can } = usePermissions();

const search = ref(props.search ?? '');
watchDebounced(search, (v) => router.get('/companies', { search: v }, { preserveState: true, replace: true, preserveScroll: true }), { debounce: 300 });

const open = ref(false);
const form = useForm({ name: '', domain: '', industry: '' });
const submit = () => form.post('/companies', { preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); } });

const columns = [
    { key: 'name', label: 'Company', sortable: true },
    { key: 'domain', label: 'Domain' },
    { key: 'industry', label: 'Industry' },
    { key: 'contacts_count', label: 'Contacts', align: 'right', mono: true },
    { key: 'health_score', label: 'Health', align: 'right' },
];
const healthColor = (n) => (n >= 70 ? 'bg-positive' : n >= 40 ? 'bg-warning' : 'bg-critical');
</script>

<template>
    <Head title="Companies" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Companies</h1>
                <p class="mt-1 text-sm text-muted">{{ companies.length }} organizations</p>
            </div>
            <Button v-if="can('companies.manage')" @click="open = true">New company</Button>
        </div>
        <div class="max-w-xs"><Input v-model="search" placeholder="Search companies…" /></div>

        <DataTable :columns="columns" :rows="companies" empty-title="No companies yet">
            <template #cell:health_score="{ value }">
                <div class="flex items-center justify-end gap-2">
                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-sunken">
                        <div class="h-full rounded-full" :class="healthColor(value)" :style="{ width: value + '%' }" />
                    </div>
                    <span class="w-7 text-right nums text-ink">{{ value }}</span>
                </div>
            </template>
        </DataTable>

        <Modal :open="open" title="New company" @close="open = false">
            <form class="space-y-4" @submit.prevent="submit">
                <Input v-model="form.name" label="Name" :error="form.errors.name" />
                <Input v-model="form.domain" label="Domain" placeholder="acme.com" />
                <Input v-model="form.industry" label="Industry" />
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">Create company</Button>
            </template>
        </Modal>
    </div>
</template>
