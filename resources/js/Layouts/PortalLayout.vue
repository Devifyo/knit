<script setup>
import { computed, onMounted, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Toast } from '@/Components/ui';
import { useToastStore } from '@/Stores/toast';

const page = usePage();
const toast = useToastStore();

const branding = computed(() => page.props.tenant?.branding ?? {});
const workspace = computed(() => page.props.tenant?.name ?? 'Customer Portal');
const contact = computed(() => page.props.portalAuth?.contact);

const applyBrand = () => {
    const color = branding.value?.brand_color;
    if (color) document.documentElement.style.setProperty('--brand', color);
};
onMounted(applyBrand);
watch(() => branding.value?.brand_color, applyBrand);

// Surface flash messages as toasts.
watch(() => page.props.flash?.success, (m) => m && toast.push({ message: m, type: 'success' }), { immediate: true });
watch(() => page.props.flash?.error, (m) => m && toast.push({ message: m, type: 'error' }), { immediate: true });

const nav = [
    { label: 'Overview', href: '/portal' },
    { label: 'Deals', href: '/portal/deals' },
    { label: 'Quotes', href: '/portal/quotes' },
    { label: 'Projects', href: '/portal/projects' },
    { label: 'Tickets', href: '/portal/tickets' },
    { label: 'Profile', href: '/portal/profile' },
];
const isActive = (href) => href === '/portal' ? page.url === '/portal' : page.url.startsWith(href);
const logout = () => router.post('/portal/logout');
</script>

<template>
    <div class="min-h-[100dvh] bg-canvas">
        <header class="border-b border-hairline bg-surface">
            <div class="mx-auto flex h-14 max-w-5xl items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex items-center gap-2.5">
                    <img v-if="branding.logo" :src="branding.logo" alt="" class="size-7 rounded-lg object-cover ring-1 ring-hairline" />
                    <span v-else class="grid size-7 place-items-center rounded-lg text-xs font-bold text-white" :style="{ background: 'var(--brand)' }">{{ workspace[0] }}</span>
                    <span class="text-sm font-semibold tracking-[-0.01em] text-ink">{{ workspace }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <Link v-for="n in nav" :key="n.href" :href="n.href" :class="['rounded-[var(--radius-control)] px-3 py-1.5 text-[13px] font-medium transition-colors', isActive(n.href) ? 'brand-wash text-[var(--brand)]' : 'text-ink-soft hover:bg-sunken']">{{ n.label }}</Link>
                    <button class="ml-1 rounded-[var(--radius-control)] px-3 py-1.5 text-[13px] font-medium text-muted hover:bg-sunken hover:text-ink" @click="logout">Sign out</button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-7 sm:px-6">
            <slot />
        </main>

        <footer class="pb-8 text-center text-xs text-faint">
            <span v-if="contact">Signed in as {{ contact.email }} · </span>Powered by Knit
        </footer>

        <Toast />
    </div>
</template>
