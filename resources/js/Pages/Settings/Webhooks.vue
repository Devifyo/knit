<script setup>
import { computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Button, Card, Input, Tag } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

defineOptions({ layout: SettingsLayout });
const props = defineProps({ eventGroups: Array, endpoints: Array, deliveries: Array });

const allEvents = () => props.eventGroups.flatMap((g) => g.events);
const allSelected = computed(() => allEvents().length > 0 && allEvents().every((e) => form.events.includes(e)));
const toggleAll = () => { form.events = allSelected.value ? [] : allEvents(); };

const { can } = usePermissions();
const manage = can('integrations.manage');

const form = useForm({ url: '', events: [] });
const add = () => form.post('/settings/webhooks', { preserveScroll: true, onSuccess: () => form.reset() });
const remove = (id) => router.delete(`/settings/webhooks/${id}`, { preserveScroll: true });
const ping = (id) => router.post(`/settings/webhooks/${id}/ping`, {}, { preserveScroll: true });
</script>

<template>
    <Head title="Developer · Webhooks" />
    <div class="space-y-6">
        <div>
            <h2 class="text-sm font-semibold tracking-[-0.01em] text-ink">Webhooks</h2>
            <p class="mt-1 text-sm text-muted">Send signed HTTP callbacks to your systems when records are created. Each request carries an <code class="rounded bg-sunken px-1 text-xs">X-Knit-Signature</code> HMAC header.</p>
        </div>

        <!-- Add endpoint -->
        <Card v-if="manage" title="Add endpoint">
            <form class="space-y-4" @submit.prevent="add">
                <Input v-model="form.url" label="Payload URL" placeholder="https://example.com/hooks/knit" :error="form.errors.url" required />
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-medium text-muted">Events <span class="text-faint">({{ form.events.length }} selected)</span></p>
                        <button type="button" class="text-xs font-medium text-[var(--brand)] hover:underline" @click="toggleAll">{{ allSelected ? 'Clear all' : 'Select all' }}</button>
                    </div>
                    <div class="space-y-3 rounded-[var(--radius-card)] border border-hairline p-3">
                        <div v-for="grp in eventGroups" :key="grp.group">
                            <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-faint">{{ grp.group }}</p>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="e in grp.events" :key="e" :class="['flex cursor-pointer items-center gap-2 rounded-[var(--radius-control)] border px-2.5 py-1.5 text-sm transition-colors', form.events.includes(e) ? 'border-[var(--brand)] brand-wash text-[var(--brand)]' : 'border-hairline text-ink-soft hover:bg-sunken']">
                                    <input v-model="form.events" type="checkbox" :value="e" class="accent-[var(--brand)]" />
                                    <span class="font-mono text-xs">{{ e }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.events" class="mt-1 text-xs text-critical">{{ form.errors.events }}</p>
                </div>
                <Button type="submit" :disabled="form.processing">Add endpoint</Button>
            </form>
        </Card>

        <!-- Endpoints -->
        <Card title="Endpoints">
            <ul v-if="endpoints.length" class="divide-y divide-hairline-soft">
                <li v-for="e in endpoints" :key="e.id" class="flex items-center justify-between gap-3 py-3">
                    <div class="min-w-0">
                        <p class="truncate font-mono text-sm text-ink">{{ e.url }}</p>
                        <div class="mt-1 flex flex-wrap gap-1.5">
                            <Tag v-for="ev in e.events" :key="ev" size="sm" color="neutral">{{ ev }}</Tag>
                            <span class="text-xs text-faint">· {{ e.deliveries }} deliveries</span>
                        </div>
                    </div>
                    <div v-if="manage" class="flex shrink-0 gap-2">
                        <Button size="sm" variant="ghost" @click="ping(e.id)">Send test</Button>
                        <Button size="sm" variant="ghost" class="text-critical" @click="remove(e.id)">Delete</Button>
                    </div>
                </li>
            </ul>
            <p v-else class="text-sm text-muted">No endpoints yet — add one to start receiving events.</p>
        </Card>

        <!-- Recent deliveries -->
        <Card title="Recent deliveries">
            <ul v-if="deliveries.length" class="divide-y divide-hairline-soft">
                <li v-for="d in deliveries" :key="d.id" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                    <div class="flex items-center gap-3">
                        <Tag :color="d.success ? 'positive' : 'critical'" size="sm">{{ d.success ? 'OK' : 'Failed' }}</Tag>
                        <span class="font-mono text-xs text-ink-soft">{{ d.event }}</span>
                        <span class="truncate text-xs text-muted">{{ d.url }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-muted nums">
                        <span v-if="d.status_code">HTTP {{ d.status_code }}</span>
                        <span>{{ d.at }}</span>
                    </div>
                </li>
            </ul>
            <p v-else class="text-sm text-muted">No deliveries yet.</p>
        </Card>
    </div>
</template>
