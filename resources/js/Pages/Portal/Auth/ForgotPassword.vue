<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: null });

const page = usePage();
const status = computed(() => page.props.flash?.success);

const form = useForm({ email: '' });
const submit = () => form.post('/portal/forgot-password');
</script>

<template>
    <Head title="Reset your password" />
    <div class="flex min-h-[100dvh] items-center justify-center bg-canvas px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="rounded-2xl border border-hairline bg-surface p-7 shadow-e1">
                <h1 class="text-lg font-semibold tracking-[-0.02em] text-ink">Reset your password</h1>
                <p class="mt-1 text-sm text-muted">Enter your email and we'll send you a link to set a new password.</p>

                <div v-if="status" class="mt-4 rounded-[var(--radius-control)] bg-positive/10 px-3 py-2.5 text-sm text-positive">{{ status }}</div>

                <form v-else class="mt-5 space-y-4" @submit.prevent="submit">
                    <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" placeholder="you@company.com" />
                    <Button type="submit" class="w-full" :loading="form.processing" @click="submit">Email me a link</Button>
                </form>

                <p class="mt-4 text-center text-xs text-muted"><Link href="/portal/login" class="text-[var(--brand)] hover:underline">Back to sign in</Link></p>
            </div>
            <p class="mt-4 text-center text-xs text-faint">Powered by Knit</p>
        </div>
    </div>
</template>
