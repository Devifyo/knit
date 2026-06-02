<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    title: String,
    current: String,
    nav: { type: Array, default: () => [] },
    html: String,
});

const currentIndex = computed(() => props.nav.findIndex((n) => n.slug === props.current));
const prev = computed(() => props.nav[currentIndex.value - 1] ?? null);
const next = computed(() => props.nav[currentIndex.value + 1] ?? null);
</script>

<template>
    <Head :title="`${title} · Guide`" />

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">
        <!-- Guide navigation -->
        <aside class="lg:sticky lg:top-6 lg:self-start">
            <p class="px-2.5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-faint">User guide</p>
            <nav class="space-y-0.5">
                <Link
                    v-for="item in nav"
                    :key="item.slug"
                    :href="item.url"
                    :class="[
                        'block truncate rounded-[var(--radius-control)] px-2.5 py-[7px] text-[13px] font-medium transition-colors',
                        item.slug === current ? 'brand-wash text-[var(--brand)]' : 'text-ink-soft hover:bg-sunken',
                    ]"
                >
                    {{ item.title }}
                </Link>
            </nav>
        </aside>

        <!-- Rendered guide -->
        <div class="min-w-0">
            <article class="guide-prose max-w-3xl" v-html="html" />

            <!-- Prev / next -->
            <div class="mt-10 flex max-w-3xl items-stretch justify-between gap-4 border-t border-hairline pt-6">
                <Link
                    v-if="prev"
                    :href="prev.url"
                    class="group flex flex-1 flex-col rounded-[var(--radius-control)] border border-hairline px-4 py-3 transition-colors hover:bg-sunken"
                >
                    <span class="text-[11px] uppercase tracking-wider text-faint">Previous</span>
                    <span class="mt-0.5 truncate text-[13px] font-medium text-ink group-hover:text-[var(--brand)]">{{ prev.title }}</span>
                </Link>
                <span v-else class="flex-1" />
                <Link
                    v-if="next"
                    :href="next.url"
                    class="group flex flex-1 flex-col rounded-[var(--radius-control)] border border-hairline px-4 py-3 text-right transition-colors hover:bg-sunken"
                >
                    <span class="text-[11px] uppercase tracking-wider text-faint">Next</span>
                    <span class="mt-0.5 truncate text-[13px] font-medium text-ink group-hover:text-[var(--brand)]">{{ next.title }}</span>
                </Link>
                <span v-else class="flex-1" />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Article styling tuned to the Knit design system (near-monochrome, hairline structure). */
.guide-prose {
    color: var(--color-ink-soft, inherit);
    font-size: 14px;
    line-height: 1.7;
}
.guide-prose :deep(h1) {
    font-size: 1.6rem;
    font-weight: 600;
    letter-spacing: -0.02em;
    color: var(--color-ink);
    margin: 0 0 0.75rem;
}
.guide-prose :deep(h2) {
    font-size: 1.2rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    color: var(--color-ink);
    margin: 2rem 0 0.6rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--color-hairline-soft, rgba(0, 0, 0, 0.06));
}
.guide-prose :deep(h3) {
    font-size: 1rem;
    font-weight: 600;
    color: var(--color-ink);
    margin: 1.4rem 0 0.4rem;
}
.guide-prose :deep(p) { margin: 0.6rem 0; }
.guide-prose :deep(a) { color: var(--brand); text-decoration: none; }
.guide-prose :deep(a:hover) { text-decoration: underline; }
.guide-prose :deep(ul),
.guide-prose :deep(ol) { margin: 0.6rem 0; padding-left: 1.4rem; }
.guide-prose :deep(li) { margin: 0.3rem 0; }
.guide-prose :deep(ul) { list-style: disc; }
.guide-prose :deep(ol) { list-style: decimal; }
.guide-prose :deep(strong) { color: var(--color-ink); font-weight: 600; }
.guide-prose :deep(blockquote) {
    margin: 1rem 0;
    padding: 0.6rem 1rem;
    border-left: 3px solid var(--brand);
    background: var(--color-sunken, rgba(0, 0, 0, 0.03));
    border-radius: 0 var(--radius-control, 8px) var(--radius-control, 8px) 0;
    color: var(--color-muted);
}
.guide-prose :deep(blockquote p) { margin: 0.2rem 0; }
.guide-prose :deep(code) {
    font-family: var(--font-mono, ui-monospace, monospace);
    font-size: 0.85em;
    background: var(--color-sunken, rgba(0, 0, 0, 0.05));
    padding: 0.12em 0.4em;
    border-radius: 5px;
}
.guide-prose :deep(pre) {
    background: var(--color-sunken, rgba(0, 0, 0, 0.05));
    border: 1px solid var(--color-hairline-soft, rgba(0, 0, 0, 0.06));
    border-radius: var(--radius-control, 8px);
    padding: 0.9rem 1rem;
    overflow-x: auto;
    margin: 1rem 0;
}
.guide-prose :deep(pre code) { background: none; padding: 0; font-size: 0.8rem; }
.guide-prose :deep(table) {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
    font-size: 13px;
}
.guide-prose :deep(th),
.guide-prose :deep(td) {
    text-align: left;
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid var(--color-hairline-soft, rgba(0, 0, 0, 0.08));
    vertical-align: top;
}
.guide-prose :deep(th) {
    font-weight: 600;
    color: var(--color-ink);
    border-bottom: 1px solid var(--color-hairline, rgba(0, 0, 0, 0.12));
}
.guide-prose :deep(td) { color: var(--color-ink-soft); }
.guide-prose :deep(hr) {
    border: none;
    border-top: 1px solid var(--color-hairline-soft, rgba(0, 0, 0, 0.08));
    margin: 1.75rem 0;
}
</style>
