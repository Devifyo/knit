<script setup>
import { Menu, MenuButton, MenuItems } from '@headlessui/vue';

defineProps({ align: { type: String, default: 'right' } });
</script>

<template>
    <Menu as="div" class="relative inline-block text-left">
        <MenuButton as="template"><slot name="trigger" /></MenuButton>
        <transition
            enter-active-class="transition duration-150 ease-[var(--ease-out-soft)]"
            enter-from-class="opacity-0 scale-95 -translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0 scale-95"
        >
            <MenuItems :class="['absolute z-40 mt-2 w-56 origin-top rounded-[var(--radius-card)] border border-hairline bg-surface p-1 shadow-e2 focus:outline-none', align === 'right' ? 'right-0' : 'left-0']">
                <slot />
            </MenuItems>
        </transition>
    </Menu>
</template>
