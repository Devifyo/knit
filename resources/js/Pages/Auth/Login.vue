<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: AuthLayout });
defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({ email: '', password: '', remember: false });

const submit = () => form.post('/login', { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Sign in" />
    <AuthLayout title="Welcome back" subtitle="Sign in to your workspace">
        <div v-if="status" class="mb-4 rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700">{{ status }}</div>

        <form class="space-y-4" @submit.prevent="submit">
            <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" required />
            <Input v-model="form.password" type="password" label="Password" :error="form.errors.password" required />

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input v-model="form.remember" type="checkbox" class="rounded border-gray-300" />
                    Remember me
                </label>
                <Link v-if="canResetPassword" href="/forgot-password" class="text-sm text-brand-600 hover:underline">
                    Forgot password?
                </Link>
            </div>

            <Button type="submit" class="w-full" :loading="form.processing">Sign in</Button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            No workspace yet?
            <Link href="/register" class="font-medium text-brand-600 hover:underline">Create one</Link>
        </p>
    </AuthLayout>
</template>
