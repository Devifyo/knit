<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: null },
    size: { type: String, default: 'md' }, // md | lg
});
const emit = defineEmits(['close']);
</script>

<template>
    <TransitionRoot :show="open" as="template">
        <Dialog class="relative z-50" @close="emit('close')">
            <TransitionChild as="template" enter="duration-150 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-100 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-zinc-900/40 backdrop-blur-[2px]" />
            </TransitionChild>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <TransitionChild as="template" enter="duration-200 ease-[var(--ease-out-soft)]" enter-from="opacity-0 scale-[0.97]" enter-to="opacity-100 scale-100" leave="duration-100 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-[0.98]">
                    <DialogPanel :class="['w-full rounded-2xl bg-surface shadow-e3', size === 'lg' ? 'max-w-3xl' : 'max-w-lg']">
                        <div v-if="title" class="border-b border-hairline-soft px-6 py-4">
                            <DialogTitle class="text-sm font-semibold tracking-[-0.01em] text-ink">{{ title }}</DialogTitle>
                        </div>
                        <div class="px-6 py-5"><slot /></div>
                        <div v-if="$slots.footer" class="flex justify-end gap-2 border-t border-hairline-soft px-6 py-4"><slot name="footer" /></div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
