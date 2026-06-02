<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: AuthLayout });
defineProps({ status: String });

const form = useForm({ email: '' });
const submit = () => form.post('/forgot-password');
</script>

<template>
    <Head title="Forgot password" />
    <AuthLayout title="Reset your password" subtitle="We'll email you a reset link">
        <div v-if="status" class="mb-4 rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700">{{ status }}</div>
        <form class="space-y-4" @submit.prevent="submit">
            <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" required />
            <Button type="submit" class="w-full" :loading="form.processing">Email reset link</Button>
        </form>
        <p class="mt-6 text-center text-sm text-gray-500">
            <Link href="/login" class="font-medium text-brand-600 hover:underline">Back to sign in</Link>
        </p>
    </AuthLayout>
</template>
