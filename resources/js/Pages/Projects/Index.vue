<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button, Card, Modal, Input, EmptyState } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

defineProps({ projects: Array });

const { can } = usePermissions();
const open = ref(false);
const form = useForm({ name: '', description: '' });
const submit = () => form.post('/projects', { onSuccess: () => { open.value = false; form.reset(); } });
</script>

<template>
    <Head title="Projects" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Projects</h1>
                <p class="mt-1 text-sm text-muted">Plan work on shared kanban boards, log time, and attach files</p>
            </div>
            <Button v-if="can('projects.manage')" @click="open = true">New project</Button>
        </div>

        <div v-if="projects.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link v-for="p in projects" :key="p.id" :href="`/projects/${p.id}`" class="block">
                <Card class="h-full transition-shadow hover:shadow-e2">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-[15px] font-semibold tracking-[-0.01em] text-ink">{{ p.name }}</h3>
                        <span class="shrink-0 text-xs font-medium text-muted nums">{{ p.progress }}%</span>
                    </div>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-hairline">
                        <div class="h-full rounded-full bg-[var(--brand)]" :style="{ width: p.progress + '%' }" />
                    </div>
                    <div class="mt-3 flex items-center justify-between text-xs text-muted">
                        <span class="nums">{{ p.done }} / {{ p.tasks }} tasks done</span>
                        <span v-if="p.owner">{{ p.owner }}</span>
                    </div>
                </Card>
            </Link>
        </div>

        <EmptyState
            v-else
            title="No projects yet"
            description="Create a project to organise tasks into a shared board."
        >
            <Button v-if="can('projects.manage')" @click="open = true">New project</Button>
        </EmptyState>

        <Modal :open="open" title="New project" @close="open = false">
            <form class="space-y-4" @submit.prevent="submit">
                <Input v-model="form.name" label="Name" placeholder="Q3 Onboarding revamp" :error="form.errors.name" required />
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Description</label>
                    <textarea v-model="form.description" rows="3" class="w-full rounded-[var(--radius-control)] bg-surface px-3 py-2 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]" />
                </div>
                <div class="flex justify-end gap-2">
                    <Button type="button" variant="secondary" @click="open = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Create</Button>
                </div>
            </form>
        </Modal>
    </div>
</template>
