<script setup>
import { onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: null });
const props = defineProps({ workspace: Object, form: Object, submitted: Boolean });

onMounted(() => {
    if (props.workspace?.brand_color) document.documentElement.style.setProperty('--brand', props.workspace.brand_color);
});

const initial = Object.fromEntries((props.form.fields || []).map((f) => [f.key, '']));
const data = useForm(initial);
const submit = () => data.post(`/forms/${props.form.slug}`, { onSuccess: () => data.reset() });
</script>

<template>
    <Head :title="form.name" />
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
                        <h1 class="text-lg font-semibold text-ink">{{ form.success }}</h1>
                    </div>
                </template>
                <template v-else>
                    <h1 class="text-lg font-semibold tracking-[-0.02em] text-ink">{{ form.name }}</h1>
                    <form class="mt-5 space-y-4" @submit.prevent="submit">
                        <Input v-for="f in form.fields" :key="f.key" v-model="data[f.key]" :label="f.label + (f.required ? ' *' : '')" :type="f.type === 'email' ? 'email' : 'text'" :error="data.errors[f.key]" />
                        <Button type="submit" class="w-full" :loading="data.processing" @click="submit">Submit</Button>
                    </form>
                </template>
            </div>
            <p class="mt-4 text-center text-xs text-faint">Powered by Knit</p>
        </div>
    </div>
</template>
