<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Button, Card, Kanban, Modal, Input, Avatar } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';
import { useToastStore } from '@/Stores/toast';

const props = defineProps({ project: Object, columns: Array, members: Array });

const { can } = usePermissions();
const toast = useToastStore();
const manage = can('projects.manage');

const clone = (v) => JSON.parse(JSON.stringify(v));
const board = ref(clone(props.columns));
watch(() => props.columns, (v) => { board.value = clone(v); });

function onCardMoved({ card, toColumn, index }) {
    router.patch(`/project-tasks/${card.id}/move`, { status: toColumn, position: index }, {
        preserveScroll: true, preserveState: true,
        onError: () => { toast.error('Could not move task'); router.reload({ only: ['columns'] }); },
    });
}

const reload = () => router.reload({ only: ['columns', 'project'] });

// New top-level task
const taskForm = useForm({ title: '', assigned_user_id: '' });
const addTask = () => taskForm.post(`/projects/${props.project.id}/tasks`, {
    preserveScroll: true, onSuccess: () => { taskForm.reset(); reload(); },
});

// Subtask
const subOpen = ref(false);
const subParent = ref(null);
const subForm = useForm({ title: '', parent_id: null });
const openSub = (card) => { subParent.value = card; subForm.reset(); subForm.parent_id = card.id; subOpen.value = true; };
const addSub = () => subForm.post(`/projects/${props.project.id}/tasks`, {
    preserveScroll: true, onSuccess: () => { subOpen.value = false; reload(); },
});

// Time logging
const timeOpen = ref(false);
const timeTask = ref(null);
const timeForm = useForm({ minutes: 30, note: '' });
const openTime = (card) => { timeTask.value = card; timeForm.reset(); timeOpen.value = true; };
const logTime = () => timeForm.post(`/project-tasks/${timeTask.value.id}/time`, {
    preserveScroll: true, onSuccess: () => { timeOpen.value = false; reload(); },
});

// File upload
const fileForm = useForm({ file: null });
const fileInput = ref(null);
const onFile = (e) => { fileForm.file = e.target.files[0]; if (fileForm.file) uploadFile(); };
const uploadFile = () => fileForm.post(`/projects/${props.project.id}/files`, {
    preserveScroll: true, forceFormData: true,
    onSuccess: () => { fileForm.reset(); if (fileInput.value) fileInput.value.value = ''; reload(); },
});

const fmtMin = (m) => m >= 60 ? `${(m / 60).toFixed(1)}h` : `${m}m`;
</script>

<template>
    <Head :title="project.name" />
    <div class="space-y-5">
        <div>
            <Link href="/projects" class="text-xs font-medium text-muted hover:text-ink">← Projects</Link>
            <div class="mt-2 flex items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">{{ project.name }}</h1>
                    <p v-if="project.description" class="mt-1 max-w-2xl text-sm text-muted">{{ project.description }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-xs text-muted">Time logged</p>
                    <p class="text-lg font-semibold tracking-[-0.01em] text-ink nums">{{ project.total_hours }}h</p>
                </div>
            </div>

            <!-- Linked CRM records -->
            <div v-if="project.deal || project.company || project.contact" class="mt-3 flex flex-wrap items-center gap-2">
                <Link v-if="project.deal" :href="`/deals/${project.deal.id}`" class="inline-flex items-center gap-1.5 rounded-full bg-sunken px-2.5 py-1 text-xs font-medium text-ink-soft ring-1 ring-hairline transition-colors hover:text-[var(--brand)]">
                    <span class="text-faint">Deal</span> {{ project.deal.name }} <span class="nums text-muted">{{ project.deal.amount }}</span>
                </Link>
                <Link v-if="project.company" :href="`/companies/${project.company.id}`" class="inline-flex items-center gap-1.5 rounded-full bg-sunken px-2.5 py-1 text-xs font-medium text-ink-soft ring-1 ring-hairline transition-colors hover:text-[var(--brand)]">
                    <span class="text-faint">Company</span> {{ project.company.name }}
                </Link>
                <Link v-if="project.contact" :href="`/contacts/${project.contact.id}`" class="inline-flex items-center gap-1.5 rounded-full bg-sunken px-2.5 py-1 text-xs font-medium text-ink-soft ring-1 ring-hairline transition-colors hover:text-[var(--brand)]">
                    <span class="text-faint">Contact</span> {{ project.contact.name }}
                </Link>
            </div>
        </div>

        <!-- Add task -->
        <Card v-if="manage">
            <form class="flex flex-wrap items-end gap-3" @submit.prevent="addTask">
                <div class="min-w-[220px] flex-1">
                    <Input v-model="taskForm.title" label="New task" placeholder="Draft kickoff agenda" :error="taskForm.errors.title" />
                </div>
                <div class="w-48">
                    <label class="mb-1.5 block text-xs font-medium text-muted">Assignee</label>
                    <select v-model="taskForm.assigned_user_id" class="h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
                        <option value="">Unassigned</option>
                        <option v-for="m in members" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>
                </div>
                <Button type="submit" :disabled="taskForm.processing">Add task</Button>
            </form>
        </Card>

        <!-- Kanban -->
        <Kanban :columns="board" @card-moved="onCardMoved">
            <template #card="{ card }">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-ink">{{ card.title }}</p>
                    <div v-if="card.subtasks.length" class="space-y-1 border-l-2 border-hairline pl-2">
                        <div v-for="s in card.subtasks" :key="s.id" class="flex items-center gap-1.5 text-xs text-muted">
                            <span :class="['size-1.5 rounded-full', s.status === 'done' ? 'bg-[var(--brand)]' : 'bg-hairline']" />
                            <span :class="s.status === 'done' ? 'line-through' : ''">{{ s.title }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-1.5">
                            <Avatar v-if="card.assignee" :name="card.assignee" class="size-5 text-[9px]" />
                            <span v-if="card.minutes" class="text-[11px] font-medium text-muted nums">{{ fmtMin(card.minutes) }}</span>
                        </div>
                        <div v-if="manage" class="flex gap-1.5 text-[11px]">
                            <button class="font-medium text-muted hover:text-ink" @click.stop="openSub(card)">+ Subtask</button>
                            <button class="font-medium text-muted hover:text-ink" @click.stop="openTime(card)">+ Time</button>
                        </div>
                    </div>
                </div>
            </template>
        </Kanban>

        <!-- Files -->
        <Card>
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold tracking-[-0.01em] text-ink">Files</h3>
                <div v-if="manage">
                    <input ref="fileInput" type="file" class="hidden" @change="onFile" />
                    <Button variant="secondary" :disabled="fileForm.processing" @click="fileInput.click()">Upload file</Button>
                </div>
            </div>
            <div v-if="project.files.length" class="mt-3 divide-y divide-hairline-soft">
                <a
                    v-for="f in project.files"
                    :key="f.id"
                    :href="`/projects/${project.id}/files/${f.id}`"
                    class="flex items-center justify-between py-2 text-sm hover:text-[var(--brand)]"
                >
                    <span class="truncate text-ink">{{ f.name }}</span>
                    <span class="shrink-0 text-xs text-muted nums">{{ f.size }}</span>
                </a>
            </div>
            <p v-else class="mt-3 text-sm text-muted">No files attached yet.</p>
        </Card>

        <!-- Subtask modal -->
        <Modal :open="subOpen" title="Add subtask" @close="subOpen = false">
            <form class="space-y-4" @submit.prevent="addSub">
                <p class="text-xs text-muted">Under <span class="font-medium text-ink">{{ subParent?.title }}</span></p>
                <Input v-model="subForm.title" label="Subtask" :error="subForm.errors.title" required />
                <div class="flex justify-end gap-2">
                    <Button type="button" variant="secondary" @click="subOpen = false">Cancel</Button>
                    <Button type="submit" :disabled="subForm.processing">Add</Button>
                </div>
            </form>
        </Modal>

        <!-- Time modal -->
        <Modal :open="timeOpen" title="Log time" @close="timeOpen = false">
            <form class="space-y-4" @submit.prevent="logTime">
                <p class="text-xs text-muted">On <span class="font-medium text-ink">{{ timeTask?.title }}</span></p>
                <Input v-model.number="timeForm.minutes" type="number" label="Minutes" :error="timeForm.errors.minutes" required />
                <Input v-model="timeForm.note" label="Note (optional)" :error="timeForm.errors.note" />
                <div class="flex justify-end gap-2">
                    <Button type="button" variant="secondary" @click="timeOpen = false">Cancel</Button>
                    <Button type="submit" :disabled="timeForm.processing">Log</Button>
                </div>
            </form>
        </Modal>
    </div>
</template>
