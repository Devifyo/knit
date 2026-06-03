<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: null });

const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post('/portal/login', { onError: () => form.reset('password') });
</script>

<template>
    <Head title="Customer portal — Sign in" />
    <div class="flex min-h-[100dvh] items-center justify-center bg-canvas px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="rounded-2xl border border-hairline bg-surface p-7 shadow-e1">
                <h1 class="text-lg font-semibold tracking-[-0.02em] text-ink">Welcome back</h1>
                <p class="mt-1 text-sm text-muted">Sign in to your customer portal.</p>
                <form class="mt-5 space-y-4" @submit.prevent="submit">
                    <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" placeholder="you@company.com" />
                    <Input v-model="form.password" type="password" label="Password" :error="form.errors.password" placeholder="••••••••" />
                    <label class="flex items-center gap-2 text-xs text-muted">
                        <input v-model="form.remember" type="checkbox" class="rounded border-hairline text-[var(--brand)] focus:ring-[var(--brand)]" /> Remember me
                    </label>
                    <Button type="submit" class="w-full" :loading="form.processing" @click="submit">Sign in</Button>
                </form>
            </div>
            <p class="mt-4 text-center text-xs text-faint">Powered by Knit</p>
        </div>
    </div>
</template>
