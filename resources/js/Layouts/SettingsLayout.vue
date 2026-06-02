<script setup>
import { Link } from '@inertiajs/vue3';

const tabs = [
    { label: 'General', href: '#' },
    { label: 'Branding', href: '#' },
    { label: 'Members & Roles', href: '#' },
    { label: 'Custom Fields', href: '#' },
    { label: 'Pipelines', href: '#' },
    { label: 'Billing', href: '#' },
];
</script>

<template>
    <div class="mx-auto max-w-5xl">
        <h1 class="mb-6 text-2xl font-semibold text-gray-900">Settings</h1>
        <div class="grid grid-cols-1 gap-8 md:grid-cols-[200px_1fr]">
            <nav class="space-y-1">
                <Link
                    v-for="tab in tabs"
                    :key="tab.label"
                    :href="tab.href"
                    class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                >
                    {{ tab.label }}
                </Link>
            </nav>
            <div>
                <slot />
            </div>
        </div>
    </div>
</template>
