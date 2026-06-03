<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Card, Tag, EmptyState } from '@/Components/ui';

defineOptions({ layout: PortalLayout });
defineProps({ projects: Array });
</script>

<template>
    <Head title="Projects" />
    <div class="space-y-5">
        <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Your projects</h1>

        <div v-if="projects.length" class="grid gap-4 sm:grid-cols-2">
            <Link v-for="p in projects" :key="p.id" :href="`/portal/projects/${p.id}`" class="block">
                <Card class="transition-shadow hover:shadow-e2">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="truncate text-sm font-semibold text-ink">{{ p.name }}</h3>
                        <Tag size="sm" :color="p.status === 'completed' ? 'positive' : 'neutral'">{{ p.status }}</Tag>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-sunken">
                        <div class="h-full rounded-full bg-[var(--brand)]" :style="{ width: p.progress + '%' }" />
                    </div>
                    <p class="mt-1.5 text-xs text-muted">{{ p.done }}/{{ p.total }} tasks done · {{ p.progress }}%</p>
                </Card>
            </Link>
        </div>
        <Card v-else flush>
            <EmptyState title="No projects yet" description="Delivery projects you're part of will appear here." />
        </Card>
    </div>
</template>
