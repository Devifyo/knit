<script setup>
import { computed, onMounted, watch, h } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Avatar, Toast, CommandPalette, Dropdown, DropdownItem } from '@/Components/ui';
import { useTenant } from '@/Composables/useTenant';
import { useEcho } from '@/Composables/useEcho';
import { useToastStore } from '@/Stores/toast';

const page = usePage();
const { tenant } = useTenant();
const toast = useToastStore();

const branding = computed(() => tenant.value?.branding ?? {});
const user = computed(() => page.props.auth?.user);

const applyBrand = () => {
    const color = branding.value?.brand_color;
    if (color) document.documentElement.style.setProperty('--brand', color);
};
onMounted(applyBrand);
watch(() => branding.value?.brand_color, applyBrand);

onMounted(() => {
    const id = tenant.value?.id;
    if (id) {
        useEcho(`tenant.${id}.notifications`, '.NoteCreated', (e) => toast.push({ message: e.message, type: 'info' }));
        useEcho(`tenant.${id}.notifications`, '.UserMentioned', (e) => {
            if (e.to_user_id === user.value?.id) toast.push({ message: e.message, type: 'info' });
        });
    }
});

// Minimal stroke icons (no emoji per design system).
const icon = (d) => () => h('svg', { viewBox: '0 0 24 24', fill: 'none', class: 'size-[18px]', stroke: 'currentColor', 'stroke-width': '1.6', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' }, [h('path', { d })]);
const icons = {
    dashboard: icon('M4 13h6V4H4v9zm0 7h6v-5H4v5zm10 0h6v-9h-6v9zm0-16v5h6V4h-6z'),
    contacts: icon('M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 21v-1a6 6 0 0112 0v1'),
    companies: icon('M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01'),
    leads: icon('M13 2L3 14h7l-1 8 10-12h-7l1-8z'),
    deals: icon('M3 7h18M3 7l2 13h14l2-13M9 11v5M15 11v5'),
    accounts: icon('M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z'),
    quotes: icon('M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM14 2v6h6M8 13h8M8 17h8'),
    tasks: icon('M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11'),
    workflows: icon('M6 3v12M6 21a3 3 0 100-6 3 3 0 000 6zM6 3a3 3 0 100 0M18 9a3 3 0 100-6 3 3 0 000 6zm0 0a9 9 0 01-9 9'),
    inbox: icon('M22 12h-6l-2 3h-4l-2-3H2M5.45 5.11L2 12v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3.45-6.89A2 2 0 0016.76 4H7.24a2 2 0 00-1.79 1.11z'),
    chat: icon('M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z'),
    tickets: icon('M3 9a2 2 0 012-2h14a2 2 0 012 2v2a2 2 0 000 4v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 000-4V9zM13 7v10'),
    kb: icon('M4 19.5A2.5 2.5 0 016.5 17H20M4 19.5A2.5 2.5 0 006.5 22H20V2H6.5A2.5 2.5 0 004 4.5v15z'),
    members: icon('M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M11 7a4 4 0 11-8 0 4 4 0 018 0zM21 21v-2a4 4 0 00-3-3.87'),
    settings: icon('M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-2.82 1.17V21a2 2 0 11-4 0v-.09A1.65 1.65 0 007 19.4l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.6 9H4.5a2 2 0 110-4h.09A1.65 1.65 0 006 4.6l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 0011 2.6V2.5a2 2 0 014 0v.09c0 .67.39 1.27 1 1.51.34.14.72.06 1-.2l.06-.06a2 2 0 112.83 2.83l-.06.06c-.26.28-.34.66-.2 1V9c.24.61.84 1 1.51 1h.09a2 2 0 010 4h-.09c-.67 0-1.27.39-1.51 1z'),
};

const groups = [
    { items: [
        { label: 'Dashboard', href: '/dashboard', icon: 'dashboard' },
        { label: 'Contacts', href: '/contacts', icon: 'contacts' },
        { label: 'Companies', href: '/companies', icon: 'companies' },
        { label: 'Leads', href: '/leads', icon: 'leads' },
        { label: 'Deals', href: '/deals', icon: 'deals' },
        { label: 'Quotes', href: '/quotes', icon: 'quotes' },
        { label: 'Accounts', href: '/accounts', icon: 'accounts' },
    ] },
    { label: 'Communicate', items: [
        { label: 'Inbox', href: '/inbox', icon: 'inbox' },
        { label: 'Team chat', href: '/chat', icon: 'chat' },
    ] },
    { label: 'Support', items: [
        { label: 'Tickets', href: '/tickets', icon: 'tickets' },
        { label: 'Knowledge base', href: '/kb', icon: 'kb' },
    ] },
    { label: 'Automate', items: [
        { label: 'Tasks', href: '/tasks', icon: 'tasks' },
        { label: 'Workflows', href: '/workflows', icon: 'workflows' },
    ] },
    { label: 'Workspace', items: [
        { label: 'Members', href: '/members', icon: 'members' },
        { label: 'Settings', href: '/settings/branding', icon: 'settings' },
    ] },
];

const commands = groups.flatMap((g) => g.items).map((n, i) => ({ id: i, label: n.label, href: n.href, group: 'Go to' }));
const logout = () => router.post('/logout');
const isActive = (href) => page.url === href || (href !== '/dashboard' && page.url.startsWith(href));
</script>

<template>
    <div class="flex min-h-[100dvh] bg-canvas">
        <!-- Sidebar -->
        <aside class="hidden w-[248px] shrink-0 flex-col border-r border-hairline bg-surface lg:flex">
            <div class="flex h-[60px] items-center gap-2.5 px-5">
                <img v-if="branding.logo" :src="branding.logo" alt="" class="size-7 rounded-lg object-cover" />
                <span v-else class="grid size-7 place-items-center rounded-lg text-sm font-bold text-white" :style="{ background: 'var(--brand)' }">{{ (branding.name || 'K')[0] }}</span>
                <span class="truncate text-[15px] font-semibold tracking-[-0.01em] text-ink">{{ branding.name || 'Knit' }}</span>
            </div>
            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-2">
                <div v-for="(group, gi) in groups" :key="gi" class="space-y-0.5">
                    <p v-if="group.label" class="px-2.5 pb-1 pt-2 text-[11px] font-medium uppercase tracking-wider text-faint">{{ group.label }}</p>
                    <Link
                        v-for="item in group.items"
                        :key="item.label"
                        :href="item.href"
                        :class="[
                            'relative flex items-center gap-2.5 rounded-[var(--radius-control)] px-2.5 py-2 text-[13px] font-medium transition-colors',
                            isActive(item.href) ? 'brand-wash text-[var(--brand)]' : 'text-ink-soft hover:bg-sunken',
                        ]"
                    >
                        <span v-if="isActive(item.href)" class="absolute left-0 top-1/2 h-5 w-0.5 -translate-y-1/2 rounded-r bg-[var(--brand)]" />
                        <component :is="icons[item.icon]" :class="isActive(item.href) ? 'text-[var(--brand)]' : 'text-faint'" />
                        {{ item.label }}
                    </Link>
                </div>
            </nav>
            <div class="border-t border-hairline-soft px-4 py-3 text-[11px] text-faint">
                <kbd class="rounded border border-hairline bg-sunken px-1.5 py-0.5 font-sans">⌘K</kbd> to search
            </div>
        </aside>

        <!-- Main -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-[60px] items-center justify-between gap-4 border-b border-hairline bg-surface/80 px-6 backdrop-blur">
                <slot name="header">
                    <h1 class="text-sm font-semibold tracking-[-0.01em] text-ink">{{ $page.props.title ?? '' }}</h1>
                </slot>
                <div class="flex items-center gap-3">
                    <Dropdown v-if="user">
                        <template #trigger>
                            <button class="flex items-center gap-2 rounded-full transition-opacity hover:opacity-80"><Avatar :name="user.name" /></button>
                        </template>
                        <div class="px-3 py-2">
                            <p class="text-sm font-medium text-ink">{{ user.name }}</p>
                            <p class="truncate text-xs text-muted">{{ user.email }}</p>
                        </div>
                        <div class="my-1 border-t border-hairline-soft" />
                        <DropdownItem href="/settings/branding">Settings</DropdownItem>
                        <DropdownItem as="button" @click="logout">Sign out</DropdownItem>
                    </Dropdown>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto">
                <div class="mx-auto max-w-[1400px] px-6 py-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>

        <Toast />
        <CommandPalette :commands="commands" />
    </div>
</template>
