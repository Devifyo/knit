<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: null });

const form = useForm({
    workspace: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => form.post('/register', { onFinish: () => form.reset('password', 'password_confirmation') });
</script>

<template>
    <Head title="Create workspace" />
    <AuthLayout title="Create your workspace" subtitle="Start your Knit CRM in seconds — no credit card needed.">
        <form class="space-y-4" @submit.prevent="submit">
            <Input v-model="form.workspace" label="Workspace name" placeholder="Acme Inc." :error="form.errors.workspace" required />
            <Input v-model="form.name" label="Your name" placeholder="Ada Lovelace" :error="form.errors.name" required />
            <Input v-model="form.email" type="email" label="Work email" placeholder="you@company.com" :error="form.errors.email" required />
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Input v-model="form.password" type="password" label="Password" placeholder="••••••••" :error="form.errors.password" required />
                <Input v-model="form.password_confirmation" type="password" label="Confirm" placeholder="••••••••" required />
            </div>

            <Button type="submit" class="w-full" :loading="form.processing">Create workspace</Button>

            <p class="text-center text-xs text-faint">
                By creating a workspace you agree to our Terms &amp; Privacy Policy.
            </p>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            Already have a workspace?
            <Link href="/login" class="font-medium text-[var(--brand)] hover:underline">Sign in</Link>
        </p>
    </AuthLayout>
</template>
