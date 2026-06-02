<script setup>
import { computed, ref } from 'vue';
import EmptyState from './EmptyState.vue';

const props = defineProps({
    // columns: [{ key, label, sortable, align, mono }]
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    rowKey: { type: String, default: 'id' },
    selectable: { type: Boolean, default: false },
    clickable: { type: Boolean, default: false },
    emptyTitle: { type: String, default: 'No records yet' },
    emptyDescription: { type: String, default: null },
});

const emit = defineEmits(['update:selected', 'row-click']);

const sortKey = ref(null);
const sortDir = ref('asc');
const selected = ref(new Set());

const sortedRows = computed(() => {
    if (!sortKey.value) return props.rows;
    const dir = sortDir.value === 'asc' ? 1 : -1;
    return [...props.rows].sort((a, b) => {
        const av = a[sortKey.value], bv = b[sortKey.value];
        return av > bv ? dir : av < bv ? -dir : 0;
    });
});

function toggleSort(col) {
    if (!col.sortable) return;
    if (sortKey.value === col.key) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    else { sortKey.value = col.key; sortDir.value = 'asc'; }
}
function toggleRow(row) {
    const id = row[props.rowKey];
    selected.value.has(id) ? selected.value.delete(id) : selected.value.add(id);
    emit('update:selected', [...selected.value]);
}
const alignCls = (c) => (c.align === 'right' ? 'text-right' : c.align === 'center' ? 'text-center' : 'text-left');
</script>

<template>
    <div class="overflow-hidden rounded-[var(--radius-card)] border border-hairline bg-surface shadow-e1">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-hairline bg-sunken/60">
                    <th v-if="selectable" class="w-10 px-4 py-2.5" />
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        :class="['px-4 py-2.5 text-xs font-medium text-muted', alignCls(col), col.sortable ? 'cursor-pointer select-none hover:text-ink-soft' : '']"
                        @click="toggleSort(col)"
                    >
                        <span class="inline-flex items-center gap-1">
                            {{ col.label }}
                            <span v-if="sortKey === col.key" class="text-[var(--brand)]">{{ sortDir === 'asc' ? '↑' : '↓' }}</span>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(row, i) in sortedRows"
                    :key="row[rowKey]"
                    :class="[
                        'border-b border-hairline-soft transition-colors last:border-0',
                        clickable ? 'cursor-pointer' : '',
                        selected.has(row[rowKey]) ? 'brand-wash' : 'hover:bg-sunken/70',
                    ]"
                    @click="clickable && emit('row-click', row)"
                >
                    <td v-if="selectable" class="px-4 py-3" @click.stop>
                        <input type="checkbox" class="rounded border-hairline text-[var(--brand)]" :checked="selected.has(row[rowKey])" @change="toggleRow(row)" />
                    </td>
                    <td
                        v-for="col in columns"
                        :key="col.key"
                        :class="['px-4 py-3 text-ink-soft', alignCls(col), col.mono ? 'nums text-ink' : '']"
                    >
                        <slot :name="`cell:${col.key}`" :row="row" :value="row[col.key]">{{ row[col.key] }}</slot>
                    </td>
                </tr>
            </tbody>
        </table>
        <EmptyState v-if="!sortedRows.length" :title="emptyTitle" :description="emptyDescription">
            <template v-if="$slots.empty" #action><slot name="empty" /></template>
        </EmptyState>
    </div>
</template>
