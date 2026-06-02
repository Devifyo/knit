<script setup>
import { Menu, MenuButton, MenuItems } from '@headlessui/vue';

defineProps({
    align: { type: String, default: 'right' }, // left | right
});
</script>

<template>
    <Menu as="div" class="relative inline-block text-left">
        <MenuButton as="template">
            <slot name="trigger" />
        </MenuButton>
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <MenuItems
                :class="[
                    'absolute z-40 mt-2 w-56 origin-top rounded-lg border border-gray-200 bg-white p-1 shadow-lg focus:outline-none',
                    align === 'right' ? 'right-0' : 'left-0',
                ]"
            >
                <slot />
            </MenuItems>
        </transition>
    </Menu>
</template>
