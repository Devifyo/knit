<script setup>
import { Head } from '@inertiajs/vue3';
import { Card, DataTable, Tag, Avatar } from '@/Components/ui';

defineProps({ members: Array });

const columns = [
    { key: 'name', label: 'Member', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'roles', label: 'Roles' },
];
</script>

<template>
    <Head title="Members" />
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-gray-900">Members &amp; Roles</h1>

        <Card title="Workspace members" subtitle="Everyone in this workspace and their roles">
            <DataTable :columns="columns" :rows="members" row-key="id">
                <template #cell:name="{ row }">
                    <div class="flex items-center gap-2">
                        <Avatar :name="row.name" size="sm" />
                        <span class="font-medium text-gray-800">{{ row.name }}</span>
                    </div>
                </template>
                <template #cell:roles="{ value }">
                    <Tag v-for="r in value" :key="r" color="brand" size="sm" class="mr-1">{{ r }}</Tag>
                </template>
            </DataTable>
        </Card>
    </div>
</template>
