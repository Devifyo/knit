<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: null });

const recovery = ref(false);
const form = useForm({ code: '', recovery_code: '' });

const submit = () => form.post('/two-factor-challenge');
</script>

<template>
    <Head title="Two-factor authentication" />
    <AuthLayout title="Two-factor authentication" :subtitle="recovery ? 'Enter a recovery code' : 'Enter your authenticator code'">
        <form class="space-y-4" @submit.prevent="submit">
            <Input v-if="!recovery" v-model="form.code" label="Authentication code" inputmode="numeric" autofocus :error="form.errors.code" />
            <Input v-else v-model="form.recovery_code" label="Recovery code" :error="form.errors.recovery_code" />

            <Button type="submit" class="w-full" :loading="form.processing">Verify</Button>
        </form>
        <button class="mt-4 w-full text-center text-sm font-medium text-[var(--brand)] hover:underline" @click="recovery = !recovery">
            {{ recovery ? 'Use authenticator code' : 'Use a recovery code' }}
        </button>
    </AuthLayout>
</template>
