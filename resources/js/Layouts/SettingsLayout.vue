<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();

// Settings is a sub-area of the app, grouped for scannability.
const groups = [
    { label: 'Workspace', items: [
        { label: 'Branding', href: '/settings/branding' },
        { label: 'Email', href: '/settings/email' },
        { label: 'Members & roles', href: '/members' },
        { label: 'Billing', href: '/settings/billing' },
    ] },
    { label: 'Security & data', items: [
        { label: 'Security', href: '/settings/security' },
        { label: 'Audit log', href: '/settings/audit' },
    ] },
    { label: 'Platform', items: [
        { label: 'Developer', href: '/settings/webhooks' },
        { label: 'Modules', href: '/settings/modules' },
    ] },
];

const isActive = (href) => page.url === href || page.url.startsWith(href + '/');
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Settings</h1>
                <p class="mt-1 text-sm text-muted">Manage your workspace, security and platform.</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-[200px_1fr]">
                <!-- Settings sub-navigation -->
                <nav class="space-y-5">
                    <div v-for="group in groups" :key="group.label" class="space-y-0.5">
                        <p class="px-2.5 pb-1 text-[11px] font-semibold uppercase tracking-wider text-faint">{{ group.label }}</p>
                        <Link
                            v-for="tab in group.items"
                            :key="tab.href"
                            :href="tab.href"
                            :class="[
                                'relative block rounded-[var(--radius-control)] px-2.5 py-[7px] text-[13px] font-medium transition-colors',
                                isActive(tab.href) ? 'brand-wash text-[var(--brand)]' : 'text-ink-soft hover:bg-sunken',
                            ]"
                        >
                            <span v-if="isActive(tab.href)" class="absolute left-0 top-1/2 h-4 w-[3px] -translate-y-1/2 rounded-r bg-[var(--brand)]" />
                            {{ tab.label }}
                        </Link>
                    </div>
                </nav>

                <!-- Active settings panel -->
                <div class="min-w-0"><slot /></div>
            </div>
        </div>
    </AppLayout>
</template>
