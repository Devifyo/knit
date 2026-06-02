<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: AuthLayout });

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
    <AuthLayout title="Create your workspace" subtitle="Start your Knit CRM in seconds">
        <form class="space-y-4" @submit.prevent="submit">
            <Input v-model="form.workspace" label="Workspace name" placeholder="Acme Inc." :error="form.errors.workspace" required />
            <Input v-model="form.name" label="Your name" :error="form.errors.name" required />
            <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" required />
            <Input v-model="form.password" type="password" label="Password" :error="form.errors.password" required />
            <Input v-model="form.password_confirmation" type="password" label="Confirm password" required />

            <Button type="submit" class="w-full" :loading="form.processing">Create workspace</Button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Already have a workspace?
            <Link href="/login" class="font-medium text-brand-600 hover:underline">Sign in</Link>
        </p>
    </AuthLayout>
</template>
