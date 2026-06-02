<script setup>
defineProps({
    title: { type: String, default: null },
    subtitle: { type: String, default: null },
});
</script>

<template>
    <div class="flex min-h-full items-center justify-center bg-gray-50 px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 flex flex-col items-center gap-2">
                <span class="grid size-11 place-items-center rounded-xl bg-brand-600 text-xl font-bold text-white">K</span>
                <h1 v-if="title" class="text-xl font-semibold text-gray-900">{{ title }}</h1>
                <p v-if="subtitle" class="text-sm text-gray-500">{{ subtitle }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
