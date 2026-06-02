<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Card, Button, Tag } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

defineOptions({ layout: SettingsLayout });
defineProps({ modules: Array });

const { can } = usePermissions();
const manage = can('modules.manage');
const toggle = (key) => router.post(`/settings/modules/${key}/toggle`, {}, { preserveScroll: true });
</script>

<template>
    <Head title="Modules" />
    <div class="space-y-5">
        <div>
            <h2 class="text-sm font-semibold tracking-[-0.01em] text-ink">Industry modules</h2>
            <p class="mt-1 text-sm text-muted">Install optional verticals for your workspace. Enabled modules appear in the sidebar.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <Card v-for="m in modules" :key="m.key" :class="m.enabled ? 'ring-1 ring-[var(--brand)]' : ''">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-[15px] font-semibold tracking-[-0.01em] text-ink">{{ m.name }}</h3>
                            <Tag v-if="m.hipaa" size="sm" color="warning">HIPAA</Tag>
                            <Tag v-if="m.enabled" size="sm" color="positive">Enabled</Tag>
                        </div>
                        <p class="mt-1 text-sm text-muted">{{ m.description }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs text-muted">
                        Adds <span class="font-medium text-ink-soft">{{ m.entity }}</span>
                        <template v-if="m.enabled"> · <span class="nums">{{ m.records }}</span> records</template>
                    </span>
                    <div class="flex gap-2">
                        <Button v-if="m.enabled" variant="secondary" size="sm" :href="m.href">Open</Button>
                        <Button v-if="manage" :variant="m.enabled ? 'ghost' : 'primary'" size="sm" :class="m.enabled ? 'text-critical' : ''" @click="toggle(m.key)">
                            {{ m.enabled ? 'Disable' : 'Enable' }}
                        </Button>
                    </div>
                </div>
            </Card>
        </div>
    </div>
</template>
