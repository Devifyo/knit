<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    // columns: [{ key, label, sortable }]
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    rowKey: { type: String, default: 'id' },
    selectable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:selected', 'row-click']);

const sortKey = ref(null);
const sortDir = ref('asc');
const selected = ref(new Set());

const sortedRows = computed(() => {
    if (!sortKey.value) return props.rows;
    const dir = sortDir.value === 'asc' ? 1 : -1;
    return [...props.rows].sort((a, b) => {
        const av = a[sortKey.value];
        const bv = b[sortKey.value];
        return av > bv ? dir : av < bv ? -dir : 0;
    });
});

function toggleSort(col) {
    if (!col.sortable) return;
    if (sortKey.value === col.key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = col.key;
        sortDir.value = 'asc';
    }
}

function toggleRow(row) {
    const id = row[props.rowKey];
    selected.value.has(id) ? selected.value.delete(id) : selected.value.add(id);
    emit('update:selected', [...selected.value]);
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th v-if="selectable" class="w-10 px-4 py-3" />
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        :class="['px-4 py-3 text-left font-medium text-gray-500', col.sortable ? 'cursor-pointer select-none' : '']"
                        @click="toggleSort(col)"
                    >
                        <span class="inline-flex items-center gap-1">
                            {{ col.label }}
                            <span v-if="sortKey === col.key">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr
                    v-for="row in sortedRows"
                    :key="row[rowKey]"
                    class="hover:bg-gray-50"
                    @click="emit('row-click', row)"
                >
                    <td v-if="selectable" class="px-4 py-3" @click.stop>
                        <input type="checkbox" :checked="selected.has(row[rowKey])" @change="toggleRow(row)" />
                    </td>
                    <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-gray-700">
                        <slot :name="`cell:${col.key}`" :row="row" :value="row[col.key]">
                            {{ row[col.key] }}
                        </slot>
                    </td>
                </tr>
                <tr v-if="!sortedRows.length">
                    <td :colspan="columns.length + (selectable ? 1 : 0)" class="px-4 py-10 text-center text-gray-400">
                        No records found.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
