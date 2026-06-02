<script setup>
import { computed, onMounted, watch } from 'vue';
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

// White-label: push the workspace brand color into the global CSS variable.
const applyBrand = () => {
    const color = branding.value?.brand_color;
    if (color) document.documentElement.style.setProperty('--brand', color);
};
onMounted(applyBrand);
watch(() => branding.value?.brand_color, applyBrand);

// Live notifications for this workspace.
onMounted(() => {
    const id = tenant.value?.id;
    if (id) {
        useEcho(`tenant.${id}.notifications`, '.NoteCreated', (e) => toast.push({ message: e.message, type: 'info' }));
    }
});

const nav = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Notes', href: '/notes' },
    { label: 'Members', href: '/members' },
    { label: 'Settings', href: '/settings/branding' },
];

const commands = nav.map((n, i) => ({ id: i, label: n.label, href: n.href, group: 'Navigate' }));
const logout = () => router.post('/logout');
</script>

<template>
    <div class="flex min-h-full bg-gray-50">
        <aside class="hidden w-60 shrink-0 flex-col border-r border-gray-200 bg-white lg:flex">
            <div class="flex h-16 items-center gap-2 border-b border-gray-100 px-5">
                <img v-if="branding.logo" :src="branding.logo" alt="" class="size-8 rounded-lg object-cover" />
                <span v-else class="grid size-8 place-items-center rounded-lg font-bold text-white" :style="{ background: 'var(--brand)' }">
                    {{ (branding.name || 'K')[0] }}
                </span>
                <span class="truncate text-lg font-semibold text-gray-900">{{ branding.name || 'Knit' }}</span>
            </div>
            <nav class="flex-1 space-y-1 p-3">
                <Link
                    v-for="item in nav"
                    :key="item.label"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                    :class="{ 'bg-gray-100 text-gray-900': $page.url.startsWith(item.href) }"
                >
                    {{ item.label }}
                </Link>
            </nav>
            <div class="border-t border-gray-100 p-3 text-xs text-gray-400">
                <kbd class="rounded border border-gray-200 bg-gray-50 px-1.5 py-0.5">⌘K</kbd> to search
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-6">
                <div>
                    <p class="text-xs text-gray-400">Workspace</p>
                    <p class="text-sm font-medium text-gray-900">{{ branding.name || tenant?.name || 'Central' }}</p>
                </div>
                <Dropdown v-if="user">
                    <template #trigger>
                        <button class="flex items-center gap-2">
                            <Avatar :name="user.name" />
                        </button>
                    </template>
                    <div class="px-3 py-2 text-sm">
                        <p class="font-medium text-gray-900">{{ user.name }}</p>
                        <p class="text-xs text-gray-500">{{ user.email }}</p>
                    </div>
                    <div class="my-1 border-t border-gray-100" />
                    <DropdownItem href="/settings/branding">Settings</DropdownItem>
                    <DropdownItem as="button" @click="logout">Sign out</DropdownItem>
                </Dropdown>
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>

        <Toast />
        <CommandPalette :commands="commands" />
    </div>
</template>
