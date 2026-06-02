<script setup>
import { Link } from '@inertiajs/vue3';
import { Avatar, Toast, CommandPalette } from '@/Components/ui';
import { useTenant } from '@/Composables/useTenant';

const { tenant } = useTenant();

const nav = [
    { label: 'Dashboard', href: '/dashboard', icon: '◧' },
    { label: 'Contacts', href: '#', icon: '☺' },
    { label: 'Leads', href: '#', icon: '✦' },
    { label: 'Deals', href: '#', icon: '◈' },
    { label: 'Accounts', href: '#', icon: '⌂' },
    { label: 'Inbox', href: '#', icon: '✉' },
    { label: 'Support', href: '#', icon: '⛑' },
    { label: 'Analytics', href: '#', icon: '◔' },
];

const commands = nav.map((n, i) => ({ id: i, label: n.label, href: n.href, group: 'Navigate' }));
</script>

<template>
    <div class="flex min-h-full bg-gray-50">
        <!-- Sidebar -->
        <aside class="hidden w-60 shrink-0 flex-col border-r border-gray-200 bg-white lg:flex">
            <div class="flex h-16 items-center gap-2 border-b border-gray-100 px-5">
                <span class="grid size-8 place-items-center rounded-lg bg-brand-600 font-bold text-white">K</span>
                <span class="text-lg font-semibold text-gray-900">Knit</span>
            </div>
            <nav class="flex-1 space-y-1 p-3">
                <Link
                    v-for="item in nav"
                    :key="item.label"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                >
                    <span class="w-4 text-center text-gray-400">{{ item.icon }}</span>
                    {{ item.label }}
                </Link>
            </nav>
            <div class="border-t border-gray-100 p-3 text-xs text-gray-400">
                <kbd class="rounded border border-gray-200 bg-gray-50 px-1.5 py-0.5">⌘K</kbd> to search
            </div>
        </aside>

        <!-- Main -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-6">
                <div>
                    <p class="text-xs text-gray-400">Workspace</p>
                    <p class="text-sm font-medium text-gray-900">{{ tenant?.name ?? 'Central' }}</p>
                </div>
                <Avatar :name="$page.props.auth?.user?.name ?? 'Guest'" />
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>

        <Toast />
        <CommandPalette :commands="commands" />
    </div>
</template>
