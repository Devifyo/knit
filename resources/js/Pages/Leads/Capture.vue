<script setup>
import { onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button, Input } from '@/Components/ui';

// Standalone public page — no app shell.
defineOptions({ layout: null });
const props = defineProps({ workspace: Object, submitted: Boolean });

onMounted(() => {
    if (props.workspace?.brand_color) document.documentElement.style.setProperty('--brand', props.workspace.brand_color);
});

const form = useForm({ name: '', email: '', phone: '', message: '' });
const submit = () => form.post(`/f/${props.workspace.slug}`, { onSuccess: () => form.reset() });
</script>

<template>
    <Head :title="`Contact ${workspace.name}`" />
    <div class="flex min-h-[100dvh] items-center justify-center bg-canvas px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-6 flex items-center gap-2.5">
                <span class="grid size-8 place-items-center rounded-lg text-sm font-bold text-white" :style="{ background: 'var(--brand)' }">{{ workspace.name[0] }}</span>
                <span class="text-lg font-semibold tracking-[-0.01em] text-ink">{{ workspace.name }}</span>
            </div>

            <div class="rounded-2xl border border-hairline bg-surface p-7 shadow-e1">
                <template v-if="submitted">
                    <div class="py-6 text-center">
                        <div class="mx-auto mb-3 grid size-11 place-items-center rounded-full bg-positive/10 text-positive">
                            <svg viewBox="0 0 24 24" fill="none" class="size-6" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </div>
                        <h1 class="text-lg font-semibold text-ink">Thanks — we'll be in touch</h1>
                        <p class="mt-1 text-sm text-muted">Your details were sent to the {{ workspace.name }} team.</p>
                    </div>
                </template>
                <template v-else>
                    <h1 class="text-lg font-semibold tracking-[-0.02em] text-ink">Get in touch</h1>
                    <p class="mt-1 text-sm text-muted">Tell us a bit about you and we'll reach out.</p>
                    <form class="mt-5 space-y-4" @submit.prevent="submit">
                        <Input v-model="form.name" label="Name" :error="form.errors.name" />
                        <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" />
                        <Input v-model="form.phone" label="Phone (optional)" />
                        <Input v-model="form.message" label="Message (optional)" />
                        <Button type="submit" class="w-full" :loading="form.processing" @click="submit">Submit</Button>
                    </form>
                </template>
            </div>
            <p class="mt-4 text-center text-xs text-faint">Powered by Knit</p>
        </div>
    </div>
</template>
