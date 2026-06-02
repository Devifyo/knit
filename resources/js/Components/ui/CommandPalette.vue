<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
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
const remote = ref([]); // record matches fetched from /search
const loading = ref(false);
let timer = null;

// Cmd/Ctrl-K toggles the palette.
onKeyStroke(['k', 'K'], (e) => {
    if (e.metaKey || e.ctrlKey) {
        e.preventDefault();
        open.value = !open.value;
    }
});

// Allow any UI affordance (e.g. the header search button) to open it.
const openPalette = () => { open.value = true; };
onMounted(() => window.addEventListener('knit:open-command-palette', openPalette));
onUnmounted(() => window.removeEventListener('knit:open-command-palette', openPalette));

// Live record search (debounced). Nav links still match client-side.
watch(query, (q) => {
    const term = q.trim();
    if (timer) clearTimeout(timer);
    if (term === '') { remote.value = []; loading.value = false; return; }
    loading.value = true;
    timer = setTimeout(async () => {
        try {
            const { data } = await window.axios.get('/search', { params: { q: term } });
            if (query.value.trim() === term) remote.value = data.results ?? [];
        } catch {
            if (query.value.trim() === term) remote.value = [];
        } finally {
            if (query.value.trim() === term) loading.value = false;
        }
    }, 200);
});

const navMatches = computed(() => query.value.trim() === ''
    ? props.commands
    : props.commands.filter((c) => c.label.toLowerCase().includes(query.value.trim().toLowerCase())));

const displayed = computed(() => [...navMatches.value, ...remote.value]);

function reset() { query.value = ''; remote.value = []; loading.value = false; }

function onSelect(command) {
    open.value = false;
    reset();
    if (command?.href) router.visit(command.href);
}
</script>

<template>
    <TransitionRoot :show="open" as="template" @after-leave="reset">
        <Dialog class="relative z-[70]" @close="open = false">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" />
            <div class="fixed inset-0 flex items-start justify-center p-4 pt-[15vh]">
                <DialogPanel class="w-full max-w-xl overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black/5">
                    <Combobox @update:model-value="onSelect">
                        <ComboboxInput
                            class="w-full border-0 bg-transparent px-4 py-3.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0"
                            placeholder="Search records or jump to…"
                            autocomplete="off"
                            @change="query = $event.target.value"
                        />
                        <ComboboxOptions v-if="displayed.length" static class="max-h-72 overflow-y-auto border-t border-gray-100 p-2">
                            <ComboboxOption
                                v-for="command in displayed"
                                :key="command.id"
                                :value="command"
                                v-slot="{ active }"
                                as="template"
                            >
                                <li :class="['flex cursor-pointer items-center justify-between gap-3 rounded-[6px] px-3 py-2 text-sm', active ? 'bg-[var(--brand)] text-white' : 'text-ink-soft']">
                                    <span class="flex min-w-0 flex-col">
                                        <span class="truncate">{{ command.label }}</span>
                                        <span v-if="command.sublabel" :class="['truncate text-xs', active ? 'text-white/70' : 'text-faint']">{{ command.sublabel }}</span>
                                    </span>
                                    <span v-if="command.group" :class="['shrink-0 text-xs', active ? 'text-white/70' : 'text-faint']">{{ command.group }}</span>
                                </li>
                            </ComboboxOption>
                        </ComboboxOptions>
                        <div v-else class="px-4 py-8 text-center text-sm text-gray-400">{{ loading ? 'Searching…' : 'No results.' }}</div>
                    </Combobox>
                </DialogPanel>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
