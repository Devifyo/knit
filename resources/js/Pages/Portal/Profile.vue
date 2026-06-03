<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Card, Button, Input } from '@/Components/ui';

defineOptions({ layout: PortalLayout });
const props = defineProps({ contact: Object });

const form = useForm({
    first_name: props.contact.first_name ?? '',
    last_name: props.contact.last_name ?? '',
    phone: props.contact.phone ?? '',
    job_title: props.contact.job_title ?? '',
});
const submit = () => form.put('/portal/profile', { preserveScroll: true });
</script>

<template>
    <Head title="Profile" />
    <div class="mx-auto max-w-xl space-y-5">
        <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Your profile</h1>
        <Card>
            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid grid-cols-2 gap-3">
                    <Input v-model="form.first_name" label="First name" :error="form.errors.first_name" />
                    <Input v-model="form.last_name" label="Last name" :error="form.errors.last_name" />
                </div>
                <Input :model-value="contact.email" label="Email" disabled hint="Contact us to change your sign-in email." />
                <Input v-model="form.phone" label="Phone" :error="form.errors.phone" />
                <Input v-model="form.job_title" label="Job title" :error="form.errors.job_title" />
                <Button type="submit" :loading="form.processing" @click="submit">Save changes</Button>
            </form>
        </Card>
    </div>
</template>
