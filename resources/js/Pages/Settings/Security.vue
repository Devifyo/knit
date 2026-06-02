<script setup>
import { useForm, router, Head } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Button, Card, Tag } from '@/Components/ui';
import { usePermissions } from '@/Composables/usePermissions';

defineOptions({ layout: SettingsLayout });
const props = defineProps({ policy: Object, twoFactor: Object, sessions: Array, current_ip: String });

const { can } = usePermissions();
const manage = can('security.manage');

const policyForm = useForm({ require_2fa: props.policy.require_2fa, allowed_ips: props.policy.allowed_ips });
const savePolicy = () => policyForm.put('/settings/security', { preserveScroll: true });

const enable2fa = () => router.post('/user/two-factor-authentication', {}, { preserveScroll: true });
const disable2fa = () => router.delete('/user/two-factor-authentication', { preserveScroll: true });
const confirmForm = useForm({ code: '' });
const confirm2fa = () => confirmForm.post('/user/confirmed-two-factor-authentication', { preserveScroll: true, onSuccess: () => confirmForm.reset() });
</script>

<template>
    <Head title="Security" />
    <div class="space-y-6">
        <!-- Two-factor authentication (per user) -->
        <Card title="Two-factor authentication" subtitle="Add a one-time code from an authenticator app">
            <template #header>
                <Tag :color="twoFactor.enabled ? 'positive' : 'neutral'" size="sm">{{ twoFactor.enabled ? 'Enabled' : 'Disabled' }}</Tag>
            </template>

            <div v-if="!twoFactor.enabled && !twoFactor.pending">
                <p class="text-sm text-muted">Protect your account with an authenticator app (TOTP).</p>
                <Button class="mt-3" @click="enable2fa">Enable 2FA</Button>
            </div>

            <div v-else-if="twoFactor.pending" class="space-y-4">
                <p class="text-sm text-ink-soft">Scan this QR code with your authenticator app, then enter the 6-digit code to confirm.</p>
                <div class="inline-block rounded-[var(--radius-card)] border border-hairline bg-white p-3" v-html="twoFactor.qr" />
                <div v-if="twoFactor.recovery_codes.length" class="rounded-[var(--radius-control)] bg-sunken p-3">
                    <p class="mb-1 text-xs font-medium text-muted">Recovery codes — store these safely</p>
                    <div class="grid grid-cols-2 gap-1 font-mono text-xs text-ink-soft">
                        <span v-for="c in twoFactor.recovery_codes" :key="c">{{ c }}</span>
                    </div>
                </div>
                <form class="flex items-end gap-2" @submit.prevent="confirm2fa">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-muted">Confirmation code</label>
                        <input v-model="confirmForm.code" inputmode="numeric" class="h-9 w-40 rounded-[var(--radius-control)] bg-surface px-3 font-mono text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]" />
                    </div>
                    <Button type="submit" :disabled="confirmForm.processing">Confirm</Button>
                </form>
                <p v-if="confirmForm.errors.code" class="text-xs text-critical">{{ confirmForm.errors.code }}</p>
            </div>

            <div v-else class="flex items-center justify-between">
                <p class="text-sm text-positive">Two-factor authentication is active on your account.</p>
                <Button variant="ghost" class="text-critical" @click="disable2fa">Disable</Button>
            </div>
        </Card>

        <!-- Workspace policy (admins) -->
        <Card v-if="manage" title="Workspace policy" subtitle="Applies to everyone in this workspace">
            <form class="space-y-4" @submit.prevent="savePolicy">
                <label class="flex items-center gap-3">
                    <input v-model="policyForm.require_2fa" type="checkbox" class="size-4 accent-[var(--brand)]" />
                    <span class="text-sm text-ink">Require two-factor authentication for all members</span>
                </label>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">IP allow-list <span class="text-faint">(one IP or CIDR per line — empty allows all)</span></label>
                    <textarea v-model="policyForm.allowed_ips" rows="3" placeholder="203.0.113.4&#10;10.0.0.0/8" class="w-full rounded-[var(--radius-control)] bg-surface px-3 py-2 font-mono text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]" />
                    <p class="mt-1 text-xs text-muted">Your current IP is <span class="font-mono text-ink-soft">{{ current_ip }}</span>.</p>
                    <p v-if="policyForm.errors.allowed_ips" class="mt-1 text-xs text-critical">{{ policyForm.errors.allowed_ips }}</p>
                </div>
                <Button type="submit" :disabled="policyForm.processing">Save policy</Button>
            </form>
        </Card>

        <!-- Recent sign-ins -->
        <Card title="Recent sign-ins" subtitle="Devices that accessed your account">
            <ul v-if="sessions.length" class="divide-y divide-hairline-soft">
                <li v-for="s in sessions" :key="s.id" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                    <span class="truncate text-ink-soft">{{ s.agent || 'Unknown device' }}</span>
                    <div class="flex shrink-0 items-center gap-3 text-xs text-muted nums">
                        <span>{{ s.ip }}</span>
                        <span>{{ s.at }}</span>
                    </div>
                </li>
            </ul>
            <p v-else class="text-sm text-muted">No sign-ins recorded yet.</p>
        </Card>
    </div>
</template>
