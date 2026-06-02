<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui';

defineOptions({ layout: null });
const props = defineProps({ workspace: Object, articles: Array, answer: String, question: String });

onMounted(() => {
    if (props.workspace?.brand_color) document.documentElement.style.setProperty('--brand', props.workspace.brand_color);
});

const ask = useForm({ question: '' });
const open = ref(null);
const submit = () => ask.post(`/help/${props.workspace.slug}/ask`, { preserveScroll: true });
</script>

<template>
    <Head :title="`${workspace.name} Help`" />
    <div class="min-h-[100dvh] bg-canvas">
        <div class="mx-auto max-w-2xl px-4 py-12">
            <div class="mb-8 flex items-center gap-2.5">
                <span class="grid size-8 place-items-center rounded-lg text-sm font-bold text-white" :style="{ background: 'var(--brand)' }">{{ workspace.name[0] }}</span>
                <span class="text-lg font-semibold tracking-[-0.01em] text-ink">{{ workspace.name }} Help Center</span>
            </div>

            <!-- AI assistant -->
            <div class="rounded-2xl border border-hairline bg-surface p-6 shadow-e1">
                <h1 class="text-lg font-semibold tracking-[-0.02em] text-ink">How can we help?</h1>
                <form class="mt-4 flex items-center gap-2" @submit.prevent="submit">
                    <input v-model="ask.question" placeholder="Ask a question…" class="h-10 flex-1 rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]" />
                    <Button type="submit" :loading="ask.processing" @click="submit">Ask</Button>
                </form>
                <div v-if="answer" class="mt-4 rounded-[var(--radius-card)] bg-sunken p-4 text-sm">
                    <p class="mb-1 text-xs font-medium text-muted">You asked: {{ question }}</p>
                    <p class="whitespace-pre-wrap text-ink-soft">{{ answer }}</p>
                </div>
            </div>

            <!-- Articles -->
            <h2 class="mb-3 mt-8 text-sm font-semibold text-ink">Browse articles</h2>
            <div class="space-y-2">
                <div v-for="a in articles" :key="a.slug" class="rounded-[var(--radius-card)] border border-hairline bg-surface">
                    <button class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-medium text-ink" @click="open = open === a.slug ? null : a.slug">
                        {{ a.title }}
                        <span class="text-faint">{{ open === a.slug ? '−' : '+' }}</span>
                    </button>
                    <p v-if="open === a.slug" class="whitespace-pre-wrap border-t border-hairline-soft px-4 py-3 text-sm text-ink-soft">{{ a.body }}</p>
                </div>
                <p v-if="!articles.length" class="text-sm text-muted">No published articles yet.</p>
            </div>
            <p class="mt-8 text-center text-xs text-faint">Powered by Knit</p>
        </div>
    </div>
</template>
