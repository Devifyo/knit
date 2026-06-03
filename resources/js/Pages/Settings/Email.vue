<script setup>
import { useForm, router, Head } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Button, Card, Input } from '@/Components/ui';

defineOptions({ layout: SettingsLayout });
const props = defineProps({ mail: Object });

const form = useForm({
    _method: 'put',
    host: props.mail.host ?? '',
    port: props.mail.port ?? 587,
    username: props.mail.username ?? '',
    password: '',
    encryption: props.mail.encryption ?? 'tls',
    from_address: props.mail.from_address ?? '',
    from_name: props.mail.from_name ?? '',
});

const selStyle = 'h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';
const submit = () => form.post('/settings/email', { preserveScroll: true });
const sendTest = () => router.post('/settings/email/test', {}, { preserveScroll: true });
</script>

<template>
    <Head title="Email" />
    <Card title="Email sending (SMTP)" subtitle="Send the workspace's email from your own server. Leave the host blank to use the platform default.">
        <div class="mb-4 rounded-[var(--radius-control)] px-3 py-2.5 text-sm" :class="mail.configured ? 'bg-positive/10 text-positive' : 'bg-warning/10 text-warning'">
            {{ mail.configured
                ? 'This workspace sends email through your own SMTP server.'
                : 'Using the platform default email. Add your SMTP below to send from your own domain.' }}
        </div>

        <form class="max-w-lg space-y-4" @submit.prevent="submit">
            <Input v-model="form.host" label="SMTP host" placeholder="smtp.yourprovider.com" :error="form.errors.host" />
            <div class="grid grid-cols-2 gap-3">
                <Input v-model="form.port" type="number" label="Port" placeholder="587" :error="form.errors.port" />
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Encryption</label>
                    <select v-model="form.encryption" :class="selStyle">
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                    </select>
                </div>
            </div>
            <Input v-model="form.username" label="Username" placeholder="apikey or you@domain.com" :error="form.errors.username" />
            <Input v-model="form.password" type="password" label="Password" :placeholder="mail.has_password ? '•••••••• (leave blank to keep)' : ''" :error="form.errors.password" />
            <div class="grid grid-cols-2 gap-3">
                <Input v-model="form.from_address" type="email" label="From address" placeholder="hello@yourdomain.com" :error="form.errors.from_address" />
                <Input v-model="form.from_name" label="From name" placeholder="Your Company" :error="form.errors.from_name" />
            </div>

            <div class="flex items-center gap-2 pt-1">
                <Button type="submit" :loading="form.processing">Save</Button>
                <Button v-if="mail.configured" type="button" variant="secondary" @click="sendTest">Send test email</Button>
            </div>
            <p class="text-xs text-faint">The password is stored encrypted. A test email is sent to your own address.</p>
        </form>
    </Card>
</template>
