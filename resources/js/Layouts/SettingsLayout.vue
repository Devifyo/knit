<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const tabs = [
    { label: 'Branding', href: '/settings/branding' },
    { label: 'Members & Roles', href: '/members' },
    { label: 'Billing', href: '/settings/billing' },
    { label: 'Security', href: '/settings/security' },
    { label: 'Audit log', href: '/settings/audit' },
    { label: 'Developer', href: '/settings/webhooks' },
];
</script>

<template>
    <div>
        <h1 class="mb-6 text-xl font-semibold tracking-[-0.02em] text-ink">Settings</h1>
        <div class="grid grid-cols-1 gap-8 md:grid-cols-[200px_1fr]">
            <nav class="space-y-0.5">
                <Link
                    v-for="tab in tabs"
                    :key="tab.label"
                    :href="tab.href"
                    :class="['block rounded-[var(--radius-control)] px-3 py-2 text-[13px] font-medium transition-colors', page.url.startsWith(tab.href) ? 'brand-wash text-[var(--brand)]' : 'text-ink-soft hover:bg-sunken']"
                >
                    {{ tab.label }}
                </Link>
            </nav>
            <div><slot /></div>
        </div>
    </div>
</template>
