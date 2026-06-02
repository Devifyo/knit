<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button, Card, Input, EmptyState } from '@/Components/ui';

defineProps({ tasks: Array });

const form = useForm({ title: '' });
const add = () => form.post('/tasks', { preserveScroll: true, onSuccess: () => form.reset() });
const toggle = (id) => router.patch(`/tasks/${id}/toggle`, {}, { preserveScroll: true });
</script>

<template>
    <Head title="Tasks" />
    <div class="mx-auto max-w-2xl space-y-5">
        <div>
            <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Tasks</h1>
            <p class="mt-1 text-sm text-muted">Your reminders and automation-created follow-ups</p>
        </div>

        <Card>
            <form class="flex items-center gap-2" @submit.prevent="add">
                <div class="flex-1"><Input v-model="form.title" placeholder="Add a task…" /></div>
                <Button :loading="form.processing" @click="add">Add</Button>
            </form>
        </Card>

        <Card v-if="tasks.length" flush>
            <ul class="divide-y divide-hairline-soft">
                <li v-for="t in tasks" :key="t.id" class="flex items-center gap-3 px-5 py-3">
                    <button
                        class="grid size-5 shrink-0 place-items-center rounded-md border transition-colors"
                        :class="t.done ? 'border-[var(--brand)] bg-[var(--brand)] text-white' : 'border-hairline hover:border-faint'"
                        @click="toggle(t.id)"
                    >
                        <svg v-if="t.done" viewBox="0 0 24 24" fill="none" class="size-3.5" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </button>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm" :class="t.done ? 'text-faint line-through' : 'text-ink'">{{ t.title }}</p>
                    </div>
                    <span v-if="t.due_at" class="shrink-0 text-xs text-muted">{{ t.due_at }}</span>
                    <span v-if="t.assignee" class="shrink-0 text-xs text-faint">{{ t.assignee }}</span>
                </li>
            </ul>
        </Card>
        <Card v-else flush><EmptyState title="No tasks yet" description="Add one above — or let a workflow create follow-ups for you." /></Card>
    </div>
</template>
