<script setup>
import { computed, useId } from 'vue';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: null },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    error: { type: String, default: null },
    hint: { type: String, default: null },
    disabled: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);

const id = useId();
const inputClasses = computed(() => [
    'block w-full rounded-lg border bg-white px-3 py-2 text-sm shadow-sm transition placeholder:text-gray-400 focus:outline-none focus:ring-2',
    props.error
        ? 'border-red-400 focus:border-red-500 focus:ring-red-200'
        : 'border-gray-300 focus:border-brand-500 focus:ring-brand-200',
    props.disabled ? 'bg-gray-50 text-gray-500' : '',
]);
</script>

<template>
    <div>
        <label v-if="label" :for="id" class="mb-1.5 block text-sm font-medium text-gray-700">
            {{ label }}
        </label>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :class="inputClasses"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
        <p v-else-if="hint" class="mt-1 text-xs text-gray-500">{{ hint }}</p>
    </div>
</template>
