<script setup>
import { storeToRefs } from 'pinia';
import { useToastStore } from '@/Stores/toast';

const store = useToastStore();
const { toasts } = storeToRefs(store);

const styles = {
    success: 'border-green-200 bg-green-50 text-green-800',
    error: 'border-red-200 bg-red-50 text-red-800',
    info: 'border-blue-200 bg-blue-50 text-blue-800',
};
</script>

<template>
    <div class="pointer-events-none fixed bottom-4 right-4 z-[60] flex w-80 flex-col gap-2">
        <transition-group
            enter-active-class="transition duration-200"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="['pointer-events-auto flex items-start gap-3 rounded-lg border px-4 py-3 text-sm shadow-md', styles[toast.type]]"
            >
                <span class="flex-1">{{ toast.message }}</span>
                <button class="text-current/60 hover:text-current" @click="store.dismiss(toast.id)">&times;</button>
            </div>
        </transition-group>
    </div>
</template>
