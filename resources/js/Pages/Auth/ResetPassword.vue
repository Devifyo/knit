<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: AuthLayout });
const props = defineProps({ email: String, token: String });

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => form.post('/reset-password', { onFinish: () => form.reset('password', 'password_confirmation') });
</script>

<template>
    <Head title="Reset password" />
    <AuthLayout title="Choose a new password">
        <form class="space-y-4" @submit.prevent="submit">
            <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" required />
            <Input v-model="form.password" type="password" label="New password" :error="form.errors.password" required />
            <Input v-model="form.password_confirmation" type="password" label="Confirm password" required />
            <Button type="submit" class="w-full" :loading="form.processing">Reset password</Button>
        </form>
    </AuthLayout>
</template>
