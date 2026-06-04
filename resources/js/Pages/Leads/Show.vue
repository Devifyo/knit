<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, Tag, Button, Avatar } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

const props = defineProps({ lead: Object, activities: Array });
const { can } = usePermissions();

const scoreAi = () => router.post(`/leads/${props.lead.id}/score`, {}, { preserveScroll: true });
const convert = () => router.post(`/leads/${props.lead.id}/convert`);
const convertToProject = () => router.post(`/leads/${props.lead.id}/convert-project`);

const statusColor = (s) => ({ new: 'info', working: 'warning', qualified: 'positive', unqualified: 'neutral' }[s] ?? 'neutral');
const scoreTone = (v) => v >= 60 ? 'text-positive' : v >= 30 ? 'text-warning' : 'text-muted';
const typeColor = { note: 'neutral', call: 'info', email: 'brand', meeting: 'warning', task: 'positive', system: 'neutral' };
</script>

<template>
    <Head :title="lead.name" />
    <div class="space-y-5">
        <Link href="/leads" class="inline-flex items-center gap-1 text-sm text-muted hover:text-ink-soft">← Leads</Link>

        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <Avatar :name="lead.name" size="lg" />
                <div>
                    <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">{{ lead.name }}</h1>
                    <div class="mt-1 flex items-center gap-2">
                        <Tag v-if="lead.converted" size="sm" color="positive" dot>converted</Tag>
                        <Tag v-else size="sm" :color="statusColor(lead.status)">{{ lead.status }}</Tag>
                        <span class="text-xs text-muted">Captured {{ lead.created_at }}</span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <Button v-if="can('leads.manage')" variant="secondary" @click="scoreAi">Re-score with AI</Button>
                <template v-if="!lead.converted && can('leads.convert')">
                    <Button v-if="can('projects.manage')" variant="secondary" @click="convertToProject">Convert to project</Button>
                    <Button @click="convert">Convert to contact</Button>
                </template>
                <Link v-if="lead.converted && lead.contact_id" :href="`/contacts/${lead.contact_id}`">
                    <Button variant="secondary">View contact</Button>
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[320px_1fr]">
            <!-- Left column -->
            <div class="space-y-5">
                <!-- Score -->
                <Card>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-muted">Lead score</p>
                            <p class="mt-1 text-3xl font-semibold tracking-[-0.02em] nums" :class="scoreTone(lead.score)">{{ lead.score }}<span class="text-base text-faint">/100</span></p>
                        </div>
                        <div class="relative grid size-14 place-items-center">
                            <svg viewBox="0 0 36 36" class="size-14 -rotate-90"><circle cx="18" cy="18" r="15.5" fill="none" stroke="currentColor" class="text-hairline" stroke-width="3" /><circle cx="18" cy="18" r="15.5" fill="none" stroke="var(--brand)" stroke-width="3" stroke-linecap="round" :stroke-dasharray="97.4" :stroke-dashoffset="97.4 - (97.4 * lead.score / 100)" /></svg>
                        </div>
                    </div>
                    <div v-if="lead.reasons?.length" class="mt-3 border-t border-hairline-soft pt-3">
                        <p class="mb-1.5 text-xs font-medium text-muted">Why this score</p>
                        <ul class="space-y-1">
                            <li v-for="(r, i) in lead.reasons" :key="i" class="flex items-start gap-2 text-sm text-ink-soft">
                                <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 size-3.5 shrink-0 text-[var(--brand)]" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5" /></svg>
                                {{ r }}
                            </li>
                        </ul>
                    </div>
                </Card>

                <!-- Details -->
                <Card>
                    <dl class="space-y-2.5 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-muted">Email</dt><dd class="truncate text-ink-soft">{{ lead.email || '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted">Phone</dt><dd class="text-ink-soft">{{ lead.phone || '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted">Source</dt><dd class="text-ink-soft">{{ lead.source || '—' }}</dd></div>
                        <div v-if="lead.source_url" class="flex justify-between gap-3">
                            <dt class="text-muted">Came from</dt>
                            <dd class="min-w-0"><a :href="lead.source_url" target="_blank" class="inline-flex items-center gap-1 truncate font-mono text-xs text-[var(--brand)] hover:underline">{{ lead.source_url }}
                                <svg viewBox="0 0 24 24" fill="none" class="size-3 shrink-0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8" /></svg></a></dd>
                        </div>
                        <div class="flex justify-between gap-3"><dt class="text-muted">Owner</dt><dd class="text-ink-soft">{{ lead.assignee || 'Unassigned' }}</dd></div>
                    </dl>
                </Card>

                <Card v-if="lead.message">
                    <p class="mb-1.5 text-xs font-medium text-muted">Message</p>
                    <p class="text-sm leading-relaxed text-ink-soft">{{ lead.message }}</p>
                </Card>
            </div>

            <!-- Activity timeline -->
            <Card title="Activity" subtitle="Everything logged against this lead">
                <ol v-if="activities.length" class="space-y-3">
                    <li v-for="a in activities" :key="a.id" class="flex items-start gap-2.5">
                        <Tag size="sm" :color="typeColor[a.type] || 'neutral'">{{ a.type }}</Tag>
                        <div class="min-w-0">
                            <p v-if="a.body" class="text-sm text-ink-soft">{{ a.body }}</p>
                            <p class="text-xs text-faint">{{ a.author }} · {{ a.at }}</p>
                        </div>
                    </li>
                </ol>
                <p v-else class="text-sm text-muted">No activity yet. Convert this lead or let a workflow log the first touch.</p>
            </Card>
        </div>
    </div>
</template>
