<script setup>
import draggable from 'vuedraggable';

defineProps({
    // columns: [{ id, title, cards: [{ id, ... }] }]
    columns: { type: Array, required: true },
});

const emit = defineEmits(['card-moved']);

function onChange(columnId, event) {
    if (event.added) {
        emit('card-moved', { card: event.added.element, toColumn: columnId, index: event.added.newIndex });
    }
}
</script>

<template>
    <div class="flex gap-4 overflow-x-auto pb-2">
        <div v-for="column in columns" :key="column.id" class="flex w-72 shrink-0 flex-col rounded-xl bg-gray-100/70 p-3">
            <div class="mb-3 flex items-center justify-between px-1">
                <h4 class="text-sm font-semibold text-gray-700">{{ column.title }}</h4>
                <span class="rounded-full bg-white px-2 py-0.5 text-xs text-gray-500">{{ column.cards.length }}</span>
            </div>
            <draggable
                :list="column.cards"
                :group="{ name: 'kanban' }"
                item-key="id"
                class="flex min-h-2 flex-1 flex-col gap-2"
                @change="(e) => onChange(column.id, e)"
            >
                <template #item="{ element }">
                    <div class="cursor-grab rounded-lg border border-gray-200 bg-white p-3 shadow-sm active:cursor-grabbing">
                        <slot name="card" :card="element">
                            <p class="text-sm font-medium text-gray-800">{{ element.title }}</p>
                        </slot>
                    </div>
                </template>
            </draggable>
        </div>
    </div>
</template>
