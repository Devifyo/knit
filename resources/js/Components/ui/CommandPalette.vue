<script setup>
import { computed, ref } from 'vue';
import { onKeyStroke } from '@vueuse/core';
import { router } from '@inertiajs/vue3';
import {
    Dialog,
    DialogPanel,
    Combobox,
    ComboboxInput,
    ComboboxOptions,
    ComboboxOption,
    TransitionRoot,
} from '@headlessui/vue';

const props = defineProps({
    // commands: [{ id, label, href, group }]
    commands: { type: Array, default: () => [] },
});

const open = ref(false);
const query = ref('');

// Cmd/Ctrl-K toggles the palette.
onKeyStroke(['k', 'K'], (e) => {
    if (e.metaKey || e.ctrlKey) {
        e.preventDefault();
        open.value = !open.value;
    }
});

const filtered = computed(() =>
    query.value === ''
        ? props.commands
        : props.commands.filter((c) => c.label.toLowerCase().includes(query.value.toLowerCase())),
);

function onSelect(command) {
    open.value = false;
    query.value = '';
    if (command?.href) router.visit(command.href);
}
</script>

<template>
    <TransitionRoot :show="open" as="template" @after-leave="query = ''">
        <Dialog class="relative z-[70]" @close="open = false">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" />
            <div class="fixed inset-0 flex items-start justify-center p-4 pt-[15vh]">
                <DialogPanel class="w-full max-w-xl overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black/5">
                    <Combobox @update:model-value="onSelect">
                        <ComboboxInput
                            class="w-full border-0 bg-transparent px-4 py-3.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0"
                            placeholder="Search or jump to…"
                            autocomplete="off"
                            @change="query = $event.target.value"
                        />
                        <ComboboxOptions v-if="filtered.length" static class="max-h-72 overflow-y-auto border-t border-gray-100 p-2">
                            <ComboboxOption
                                v-for="command in filtered"
                                :key="command.id"
                                :value="command"
                                v-slot="{ active }"
                                as="template"
                            >
                                <li :class="['flex cursor-pointer items-center justify-between rounded-[6px] px-3 py-2 text-sm', active ? 'bg-[var(--brand)] text-white' : 'text-ink-soft']">
                                    <span>{{ command.label }}</span>
                                    <span v-if="command.group" :class="active ? 'text-white/70' : 'text-faint'" class="text-xs">{{ command.group }}</span>
                                </li>
                            </ComboboxOption>
                        </ComboboxOptions>
                        <div v-else class="px-4 py-8 text-center text-sm text-gray-400">No results.</div>
                    </Combobox>
                </DialogPanel>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
