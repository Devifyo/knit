<script setup>
import draggable from 'vuedraggable';

defineProps({
    // columns: [{ id, title, count, probability, amount, cards: [...] }]
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
    <div class="flex gap-4 overflow-x-auto pb-3">
        <div v-for="column in columns" :key="column.id" class="flex w-[300px] shrink-0 flex-col rounded-[var(--radius-card)] bg-sunken/70 p-2.5">
            <div class="mb-2.5 px-1.5">
                <div class="flex items-center justify-between">
                    <h4 class="text-[13px] font-semibold tracking-[-0.01em] text-ink-soft">{{ column.title }}</h4>
                    <span class="rounded-full bg-surface px-2 py-0.5 text-[11px] font-medium text-muted ring-1 ring-hairline">{{ column.cards.length }}</span>
                </div>
                <div v-if="column.probability != null" class="mt-2 h-1 overflow-hidden rounded-full bg-hairline">
                    <div class="h-full rounded-full bg-[var(--brand)] opacity-70" :style="{ width: column.probability + '%' }" />
                </div>
            </div>
            <draggable
                :list="column.cards"
                :group="{ name: 'kanban' }"
                item-key="id"
                ghost-class="opacity-40"
                drag-class="rotate-2"
                class="flex min-h-3 flex-1 flex-col gap-2"
                @change="(e) => onChange(column.id, e)"
            >
                <template #item="{ element }">
                    <div class="cursor-grab rounded-[10px] border border-hairline bg-surface p-3 shadow-e1 transition-shadow active:cursor-grabbing hover:shadow-e2">
                        <slot name="card" :card="element">
                            <p class="text-sm font-medium text-ink">{{ element.title }}</p>
                        </slot>
                    </div>
                </template>
            </draggable>
        </div>
    </div>
</template>
