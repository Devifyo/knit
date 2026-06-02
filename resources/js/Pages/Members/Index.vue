<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, DataTable, Tag, Avatar, Button, Input, Modal } from '@/Components/ui';

const props = defineProps({ members: Array, invitations: Array, roles: Array, canInvite: Boolean });

const open = ref(false);
const form = useForm({ email: '', role: 'Agent' });
const submit = () => form.post('/members/invite', { preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); } });
const revoke = (id) => router.delete(`/invitations/${id}`, { preserveScroll: true });
const copy = (link) => navigator.clipboard?.writeText(link);

const selStyle = 'h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';
const columns = [
    { key: 'name', label: 'Member', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'roles', label: 'Roles' },
];
</script>

<template>
    <Head title="Members" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Members &amp; Roles</h1>
                <p class="mt-1 text-sm text-muted">Invite teammates and manage their access</p>
            </div>
            <Button v-if="canInvite" @click="open = true">Invite member</Button>
        </div>

        <Card title="Workspace members" flush>
            <DataTable :columns="columns" :rows="members" row-key="id">
                <template #cell:name="{ row }">
                    <div class="flex items-center gap-2.5">
                        <Avatar :name="row.name" size="sm" />
                        <span class="font-medium text-ink">{{ row.name }}</span>
                    </div>
                </template>
                <template #cell:roles="{ value }">
                    <Tag v-for="r in value" :key="r" color="brand" size="sm" class="mr-1">{{ r }}</Tag>
                </template>
            </DataTable>
        </Card>

        <Card v-if="invitations.length" title="Pending invitations" subtitle="Share the link — the invitee sets their password and joins this workspace">
            <ul class="divide-y divide-hairline-soft">
                <li v-for="inv in invitations" :key="inv.id" class="flex flex-wrap items-center gap-3 py-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-ink">{{ inv.email }}</p>
                        <a :href="inv.link" class="truncate font-mono text-xs text-[var(--brand)] hover:underline">{{ inv.link }}</a>
                    </div>
                    <Tag size="sm" :color="inv.expired ? 'critical' : 'neutral'">{{ inv.expired ? 'expired' : inv.role }}</Tag>
                    <button class="rounded-md px-2 py-1 text-xs text-ink-soft ring-1 ring-hairline hover:bg-sunken" @click="copy(inv.link)">Copy</button>
                    <button class="rounded-md px-2 py-1 text-xs text-critical ring-1 ring-critical/20 hover:bg-critical/10" @click="revoke(inv.id)">Revoke</button>
                </li>
            </ul>
        </Card>

        <Modal :open="open" title="Invite a member" @close="open = false">
            <form class="space-y-4" @submit.prevent="submit">
                <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" />
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Role</label>
                    <select v-model="form.role" :class="selStyle">
                        <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                    </select>
                </div>
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">Create invitation</Button>
            </template>
        </Modal>
    </div>
</template>
