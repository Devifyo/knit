<script setup>
defineProps({
    title: { type: String, default: null },
    subtitle: { type: String, default: null },
});
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div v-if="title || $slots.header" class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <div>
                <h3 v-if="title" class="text-sm font-semibold text-gray-900">{{ title }}</h3>
                <p v-if="subtitle" class="text-xs text-gray-500">{{ subtitle }}</p>
            </div>
            <slot name="header" />
        </div>
        <div class="p-5">
            <slot />
        </div>
        <div v-if="$slots.footer" class="border-t border-gray-100 px-5 py-3">
            <slot name="footer" />
        </div>
    </div>
</template>
