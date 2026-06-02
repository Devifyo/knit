<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Card, Avatar, Tag, Button, Input } from '@/Components/ui';

const props = defineProps({ contact: Object, customFields: Array });

const note = useForm({ body: '' });
const addNote = () => note.post(`/contacts/${props.contact.id}/notes`, { preserveScroll: true, onSuccess: () => note.reset() });

const fieldLabel = (key) => props.customFields.find((f) => f.key === key)?.label ?? key;
const typeColor = { note: 'neutral', call: 'info', email: 'brand', meeting: 'warning', task: 'positive', system: 'neutral' };
</script>

<template>
    <Head :title="contact.name" />
    <div class="space-y-5">
        <Link href="/contacts" class="inline-flex items-center gap-1 text-sm text-muted hover:text-ink-soft">← Contacts</Link>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[320px_1fr]">
            <!-- Profile -->
            <div class="space-y-5">
                <Card>
                    <div class="flex flex-col items-center text-center">
                        <Avatar :name="contact.name" size="lg" />
                        <h1 class="mt-3 text-lg font-semibold tracking-[-0.01em] text-ink">{{ contact.name }}</h1>
                        <p v-if="contact.job_title" class="text-sm text-muted">{{ contact.job_title }}<span v-if="contact.company"> · {{ contact.company }}</span></p>
                        <div class="mt-2 flex flex-wrap justify-center gap-1">
                            <Tag v-for="t in contact.tags" :key="t.name" size="sm" color="brand">{{ t.name }}</Tag>
                        </div>
                    </div>
                    <dl class="mt-5 space-y-2.5 border-t border-hairline-soft pt-4 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-muted">Email</dt><dd class="truncate text-ink-soft">{{ contact.email || '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted">Phone</dt><dd class="text-ink-soft">{{ contact.phone || '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted">Stage</dt><dd><Tag size="sm" color="info">{{ contact.lifecycle_stage }}</Tag></dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted">Source</dt><dd class="text-ink-soft">{{ contact.source || '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted">Owner</dt><dd class="text-ink-soft">{{ contact.owner || '—' }}</dd></div>
                        <div v-for="(val, key) in contact.custom_fields" :key="key" class="flex justify-between gap-3">
                            <dt class="text-muted">{{ fieldLabel(key) }}</dt><dd class="text-ink-soft">{{ val || '—' }}</dd>
                        </div>
                    </dl>
                </Card>
            </div>

            <div class="space-y-5">
            <!-- Deals -->
            <Card title="Deals" subtitle="Opportunities linked to this contact">
                <ul v-if="contact.deals?.length" class="divide-y divide-hairline-soft">
                    <li v-for="d in contact.deals" :key="d.id" class="flex items-center justify-between py-2.5">
                        <Link :href="`/deals/${d.id}`" class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink hover:text-[var(--brand)]">{{ d.name }}</p>
                            <p class="text-xs text-muted">{{ d.stage }}<span v-if="d.quotes"> · {{ d.quotes }} quote{{ d.quotes === 1 ? '' : 's' }}</span></p>
                        </Link>
                        <div class="flex items-center gap-2">
                            <span class="nums text-sm text-ink">{{ d.amount }}</span>
                            <Tag size="sm" :color="d.status === 'won' ? 'positive' : d.status === 'lost' ? 'critical' : 'info'">{{ d.status }}</Tag>
                        </div>
                    </li>
                </ul>
                <p v-else class="text-sm text-muted">No deals yet.</p>
            </Card>

            <!-- Timeline -->
            <Card title="Timeline" subtitle="Notes, calls, emails and system events">
                <form class="mb-5 flex items-start gap-2" @submit.prevent="addNote">
                    <div class="flex-1"><Input v-model="note.body" placeholder="Add a note…" /></div>
                    <Button :loading="note.processing" @click="addNote">Add</Button>
                </form>
                <ol v-if="contact.activities.length" class="relative space-y-4 border-l border-hairline-soft pl-5">
                    <li v-for="a in contact.activities" :key="a.id" class="relative">
                        <span class="absolute -left-[22px] top-1 size-2.5 rounded-full ring-2 ring-surface" :style="{ background: 'var(--brand)' }" />
                        <div class="flex items-center gap-2">
                            <Tag size="sm" :color="typeColor[a.type] ?? 'neutral'">{{ a.type }}</Tag>
                            <span class="text-xs text-faint">{{ a.author || 'System' }} · {{ a.at }}</span>
                        </div>
                        <p v-if="a.body" class="mt-1 text-sm text-ink-soft">{{ a.body }}</p>
                    </li>
                </ol>
                <p v-else class="text-sm text-muted">No activity yet — add the first note above.</p>
            </Card>
            </div>
        </div>
    </div>
</template>
