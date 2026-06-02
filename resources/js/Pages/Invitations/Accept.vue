<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: null });
const props = defineProps({ token: String, email: String, role: String, workspace: String });

const form = useForm({ name: '', password: '', password_confirmation: '' });
const submit = () => form.post(`/invite/${props.token}`);
</script>

<template>
    <Head title="Join workspace" />
    <div class="flex min-h-[100dvh] items-center justify-center bg-canvas px-4 py-12">
        <div class="w-full max-w-[400px]">
            <div class="mb-8 text-center">
                <span class="mx-auto mb-3 grid size-10 place-items-center rounded-xl text-lg font-bold text-white" :style="{ background: 'var(--brand)' }">{{ (workspace || 'K')[0] }}</span>
                <h1 class="text-lg font-semibold tracking-[-0.02em] text-ink">Join {{ workspace }}</h1>
                <p class="mt-1 text-sm text-muted">You've been invited as <strong>{{ role }}</strong> · {{ email }}</p>
            </div>
            <div class="rounded-2xl border border-hairline bg-surface p-7 shadow-e1">
                <form class="space-y-4" @submit.prevent="submit">
                    <Input v-model="form.name" label="Your name" :error="form.errors.name" />
                    <Input v-model="form.password" type="password" label="Password" :error="form.errors.password" />
                    <Input v-model="form.password_confirmation" type="password" label="Confirm password" />
                    <Button type="submit" class="w-full" :loading="form.processing" @click="submit">Join workspace</Button>
                </form>
            </div>
        </div>
    </div>
</template>
