<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: null });
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
        <div v-if="status" class="mb-4 rounded-[var(--radius-control)] bg-positive/10 px-3 py-2 text-sm text-positive">{{ status }}</div>

        <form class="space-y-4" @submit.prevent="submit">
            <Input v-model="form.email" type="email" label="Email" placeholder="you@company.com" :error="form.errors.email" required />
            <Input v-model="form.password" type="password" label="Password" placeholder="••••••••" :error="form.errors.password" required />

            <div class="flex items-center justify-between">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
                    <input v-model="form.remember" type="checkbox" class="size-4 rounded border-hairline accent-[var(--brand)]" />
                    Remember me
                </label>
                <Link v-if="canResetPassword" href="/forgot-password" class="text-sm font-medium text-[var(--brand)] hover:underline">
                    Forgot password?
                </Link>
            </div>

            <Button type="submit" class="w-full" :loading="form.processing">Sign in</Button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            No workspace yet?
            <Link href="/register" class="font-medium text-[var(--brand)] hover:underline">Create one</Link>
        </p>
    </AuthLayout>
</template>
