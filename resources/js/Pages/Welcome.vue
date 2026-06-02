<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Button, Card, Tag, Input, Modal, DataTable } from '@/Components/ui';
import { useToastStore } from '@/Stores/toast';

defineProps({
    phase: String,
    laravelVersion: String,
    phpVersion: String,
});

const toast = useToastStore();
const modalOpen = ref(false);
const sample = ref('');

const stack = [
    { layer: 'Backend', choice: 'Laravel 13 · PHP 8.4', status: 'ready' },
    { layer: 'Frontend', choice: 'Vue 3 + Inertia + Vite', status: 'ready' },
    { layer: 'Styling', choice: 'TailwindCSS v4', status: 'ready' },
    { layer: 'Real-time', choice: 'Laravel Reverb', status: 'ready' },
    { layer: 'Queue/Cache', choice: 'Redis + Horizon', status: 'ready' },
    { layer: 'Database', choice: 'MySQL (latest)', status: 'ready' },
    { layer: 'Multi-tenancy', choice: 'stancl/tenancy', status: 'ready' },
];

const columns = [
    { key: 'layer', label: 'Layer', sortable: true },
    { key: 'choice', label: 'Choice', sortable: true },
    { key: 'status', label: 'Status' },
];
</script>

<template>
    <Head title="Foundation" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <Tag color="brand">{{ phase }}</Tag>
                <h1 class="mt-2 text-2xl font-semibold text-gray-900">Knit CRM — Foundation is live</h1>
                <p class="text-sm text-gray-500">Laravel {{ laravelVersion }} · PHP {{ phpVersion }}</p>
            </div>
            <div class="flex gap-2">
                <Button variant="secondary" @click="modalOpen = true">Open modal</Button>
                <Button @click="toast.success('Toast + Pinia + Reverb-ready 🎉')">Fire toast</Button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <Card title="UI Library" subtitle="Vue 3 components">
                <p class="text-3xl font-semibold text-gray-900">12</p>
                <p class="text-xs text-gray-500">Button, Card, Modal, DataTable, Kanban, CommandPalette…</p>
            </Card>
            <Card title="Composables" subtitle="Shared logic">
                <p class="text-3xl font-semibold text-gray-900">4</p>
                <p class="text-xs text-gray-500">useEcho, useTenant, usePermissions, useTable</p>
            </Card>
            <Card title="Demo input" subtitle="Two-way binding">
                <Input v-model="sample" label="Try typing" placeholder="Hello Knit" />
                <p class="mt-2 text-xs text-gray-500">Value: {{ sample || '—' }}</p>
            </Card>
        </div>

        <Card title="Tech stack" subtitle="Everything wired in Phase 0">
            <DataTable :columns="columns" :rows="stack" row-key="layer">
                <template #cell:status="{ value }">
                    <Tag color="green">{{ value }}</Tag>
                </template>
            </DataTable>
        </Card>

        <Modal :open="modalOpen" title="Component library check" @close="modalOpen = false">
            <p class="text-sm text-gray-600">
                This Modal, the Toast, DataTable, Card, Tag, Input and Button are all from
                <code class="rounded bg-gray-100 px-1">@/Components/ui</code>. Headless UI + Tailwind v4 confirmed working.
            </p>
            <template #footer>
                <Button variant="secondary" @click="modalOpen = false">Close</Button>
                <Button @click="modalOpen = false">Got it</Button>
            </template>
        </Modal>
    </div>
</template>
