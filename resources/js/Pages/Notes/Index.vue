<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import { Button, Card, Input, DataTable } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

defineProps({ notes: Array });

const { can } = usePermissions();
const form = useForm({ title: '', body: '' });

const submit = () => form.post('/notes', { onSuccess: () => form.reset() });
const destroy = (id) => form.delete(`/notes/${id}`, { preserveScroll: true });

const columns = [
    { key: 'title', label: 'Title', sortable: true },
    { key: 'author', label: 'Author' },
    { key: 'created_at', label: 'Created' },
    { key: 'actions', label: '' },
];
</script>

<template>
    <Head title="Notes" />
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-gray-900">Notes</h1>

        <Card v-if="can('notes.create')" title="New note">
            <form class="space-y-3" @submit.prevent="submit">
                <Input v-model="form.title" label="Title" :error="form.errors.title" />
                <Input v-model="form.body" label="Body" :error="form.errors.body" />
                <Button type="submit" :loading="form.processing">Add note</Button>
            </form>
        </Card>

        <Card title="Workspace notes" subtitle="Scoped to your workspace — other tenants' notes are never visible">
            <DataTable :columns="columns" :rows="notes" row-key="id">
                <template #cell:actions="{ row }">
                    <Button v-if="can('notes.delete')" variant="ghost" size="sm" @click="destroy(row.id)">Delete</Button>
                </template>
            </DataTable>
        </Card>
    </div>
</template>
