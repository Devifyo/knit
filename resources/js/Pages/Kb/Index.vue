<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, Button, Input, Tag, EmptyState } from '@/Components/ui';

defineProps({ articles: Array, portalUrl: String });

const form = useForm({ title: '', body: '' });
const submit = () => form.post('/kb', { preserveScroll: true, onSuccess: () => form.reset() });
const destroy = (id) => router.delete(`/kb/${id}`, { preserveScroll: true });
</script>

<template>
    <Head title="Knowledge base" />
    <div class="space-y-5">
        <div>
            <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Knowledge base</h1>
            <p class="mt-1 text-sm text-muted">Public self-service portal: <a :href="portalUrl" target="_blank" class="font-mono text-[var(--brand)] hover:underline">{{ portalUrl }}</a></p>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_340px]">
            <Card flush>
                <ul v-if="articles.length" class="divide-y divide-hairline-soft">
                    <li v-for="a in articles" :key="a.id" class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-ink">{{ a.title }}</p>
                            <p class="font-mono text-xs text-muted">/{{ a.slug }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Tag size="sm" :color="a.published ? 'positive' : 'neutral'">{{ a.published ? 'published' : 'draft' }}</Tag>
                            <button class="text-faint hover:text-critical" @click="destroy(a.id)">✕</button>
                        </div>
                    </li>
                </ul>
                <EmptyState v-else title="No articles yet" description="Write your first help article — it shows on the public portal and feeds the AI chatbot." />
            </Card>

            <Card title="New article">
                <form class="space-y-3" @submit.prevent="submit">
                    <Input v-model="form.title" label="Title" :error="form.errors.title" />
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted">Body</label>
                        <textarea v-model="form.body" rows="6" class="w-full rounded-[var(--radius-control)] bg-surface p-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]" />
                        <p v-if="form.errors.body" class="mt-1 text-xs text-critical">{{ form.errors.body }}</p>
                    </div>
                    <Button :loading="form.processing" @click="submit">Publish</Button>
                </form>
            </Card>
        </div>
    </div>
</template>
