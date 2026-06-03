<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Card, Tag } from '@/Components/ui';

defineOptions({ layout: PortalLayout });
defineProps({ project: Object, columns: Array });
</script>

<template>
    <Head :title="project.name" />
    <div class="space-y-5">
        <Link href="/portal/projects" class="text-xs text-muted hover:text-ink">← All projects</Link>

        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold tracking-[-0.02em] text-ink">{{ project.name }}</h1>
                <p v-if="project.description" class="mt-0.5 text-sm text-muted">{{ project.description }}</p>
            </div>
            <Tag size="sm" :color="project.status === 'completed' ? 'positive' : 'neutral'">{{ project.status }}</Tag>
        </div>

        <div>
            <div class="h-2 overflow-hidden rounded-full bg-sunken">
                <div class="h-full rounded-full bg-[var(--brand)]" :style="{ width: project.progress + '%' }" />
            </div>
            <p class="mt-1.5 text-xs text-muted">{{ project.progress }}% complete</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <Card v-for="col in columns" :key="col.id" :title="col.title">
                <template #actions><span class="text-xs text-faint">{{ col.tasks.length }}</span></template>
                <ul v-if="col.tasks.length" class="space-y-2">
                    <li v-for="t in col.tasks" :key="t.id" class="rounded-[var(--radius-control)] border border-hairline bg-surface p-2.5">
                        <p class="text-sm text-ink">{{ t.title }}</p>
                        <ul v-if="t.subtasks.length" class="mt-1.5 space-y-1">
                            <li v-for="(s, i) in t.subtasks" :key="i" class="flex items-center gap-1.5 text-xs text-muted">
                                <span class="size-1.5 rounded-full" :class="s.done ? 'bg-positive' : 'bg-hairline'" />
                                <span :class="s.done ? 'line-through text-faint' : ''">{{ s.title }}</span>
                            </li>
                        </ul>
                    </li>
                </ul>
                <p v-else class="text-xs text-faint">—</p>
            </Card>
        </div>
    </div>
</template>
