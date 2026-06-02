<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: null });
defineProps({ status: String });

const form = useForm({ email: '' });
const submit = () => form.post('/forgot-password');
</script>

<template>
    <Head title="Forgot password" />
    <AuthLayout title="Reset your password" subtitle="We'll email you a reset link">
        <div v-if="status" class="mb-4 rounded-[var(--radius-control)] bg-positive/10 px-3 py-2 text-sm text-positive">{{ status }}</div>
        <form class="space-y-4" @submit.prevent="submit">
            <Input v-model="form.email" type="email" label="Email" placeholder="you@company.com" :error="form.errors.email" required />
            <Button type="submit" class="w-full" :loading="form.processing">Email reset link</Button>
        </form>
        <p class="mt-6 text-center text-sm text-muted">
            <Link href="/login" class="font-medium text-[var(--brand)] hover:underline">Back to sign in</Link>
        </p>
    </AuthLayout>
</template>
