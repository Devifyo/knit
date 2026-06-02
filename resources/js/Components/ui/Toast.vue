<script setup>
import { storeToRefs } from 'pinia';
import { useToastStore } from '@/Stores/toast';

const store = useToastStore();
const { toasts } = storeToRefs(store);

const styles = {
    success: 'ring-positive/20 text-ink',
    error: 'ring-critical/25 text-ink',
    info: 'ring-hairline text-ink',
};
const dot = { success: 'bg-positive', error: 'bg-critical', info: 'bg-[var(--brand)]' };
</script>

<template>
    <div class="pointer-events-none fixed bottom-5 right-5 z-[60] flex w-80 flex-col gap-2">
        <transition-group
            enter-active-class="transition duration-200 ease-[var(--ease-out-soft)]"
            enter-from-class="translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="['pointer-events-auto flex items-start gap-2.5 rounded-[var(--radius-control)] bg-surface px-3.5 py-3 text-sm shadow-e2 ring-1', styles[toast.type]]"
            >
                <span class="mt-1.5 size-1.5 shrink-0 rounded-full" :class="dot[toast.type]" />
                <span class="flex-1">{{ toast.message }}</span>
                <button class="text-faint hover:text-ink-soft" @click="store.dismiss(toast.id)">&times;</button>
            </div>
        </transition-group>
    </div>
</template>
