<script setup>
import { onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button, Input } from '@/Components/ui';

defineOptions({ layout: null });

const props = defineProps({ token: String, email: String, name: String, workspace: Object });

onMounted(() => {
    if (props.workspace?.brand_color) document.documentElement.style.setProperty('--brand', props.workspace.brand_color);
});

const form = useForm({ password: '', password_confirmation: '' });
const submit = () => form.post(`/portal/activate/${props.token}`, { onError: () => form.reset('password', 'password_confirmation') });
</script>

<template>
    <Head title="Set your password" />
    <div class="flex min-h-[100dvh] items-center justify-center bg-canvas px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="rounded-2xl border border-hairline bg-surface p-7 shadow-e1">
                <h1 class="text-lg font-semibold tracking-[-0.02em] text-ink">Set your password</h1>
                <p class="mt-1 text-sm text-muted">
                    {{ workspace?.name ? `Activate your ${workspace.name} portal account` : 'Activate your portal account' }}<span v-if="email"> for <span class="font-medium text-ink-soft">{{ email }}</span></span>.
                </p>
                <form class="mt-5 space-y-4" @submit.prevent="submit">
                    <Input v-model="form.password" type="password" label="New password" :error="form.errors.password" placeholder="At least 8 characters" />
                    <Input v-model="form.password_confirmation" type="password" label="Confirm password" placeholder="••••••••" />
                    <Button type="submit" class="w-full" :loading="form.processing" @click="submit">Activate &amp; sign in</Button>
                </form>
            </div>
            <p class="mt-4 text-center text-xs text-faint">Powered by Knit</p>
        </div>
    </div>
</template>
