<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: null },
});

const emit = defineEmits(['close']);
</script>

<template>
    <TransitionRoot :show="open" as="template">
        <Dialog class="relative z-50" @close="emit('close')">
            <TransitionChild
                as="template"
                enter="ease-out duration-200"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-150"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 flex items-center justify-center p-4">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-200"
                    enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100"
                    leave="ease-in duration-150"
                    leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95"
                >
                    <DialogPanel class="w-full max-w-lg rounded-xl bg-white shadow-xl">
                        <div v-if="title" class="border-b border-gray-100 px-6 py-4">
                            <DialogTitle class="text-base font-semibold text-gray-900">{{ title }}</DialogTitle>
                        </div>
                        <div class="px-6 py-5">
                            <slot />
                        </div>
                        <div v-if="$slots.footer" class="flex justify-end gap-2 border-t border-gray-100 px-6 py-4">
                            <slot name="footer" />
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
