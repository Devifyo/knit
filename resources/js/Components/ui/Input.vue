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
    'block w-full rounded-[var(--radius-control)] bg-surface px-3 h-9 text-sm text-ink ring-1 ring-inset transition-[box-shadow] duration-150 placeholder:text-faint focus:outline-none',
    props.error
        ? 'ring-critical/60 focus:ring-critical'
        : 'ring-hairline focus:ring-2 focus:ring-[var(--brand)]',
    props.disabled ? 'bg-sunken text-muted' : '',
]);
</script>

<template>
    <div>
        <label v-if="label" :for="id" class="mb-1.5 block text-xs font-medium text-muted">
            {{ label }}
        </label>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :class="inputClasses"
            v-bind="$attrs"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <p v-if="error" class="mt-1 text-xs text-critical">{{ error }}</p>
        <p v-else-if="hint" class="mt-1 text-xs text-muted">{{ hint }}</p>
    </div>
</template>
