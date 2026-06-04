<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Card, DataTable, Tag, Avatar, Button, Input, Modal } from '@/Components/ui';

defineOptions({ layout: SettingsLayout });
const props = defineProps({
    members: Array, invitations: Array, roles: Array, ownerRole: String,
    canInvite: Boolean, canManageRoles: Boolean, permissionGroups: Array, rolePermissions: Object,
});

// ---- Add member (create directly or send an invite link) ----
const open = ref(false);
const mode = ref('create');
const form = useForm({ name: '', email: '', role: 'Agent' });
const submit = () => form.post(mode.value === 'create' ? '/members' : '/members/invite', {
    preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); },
});
const revoke = (id) => router.delete(`/invitations/${id}`, { preserveScroll: true });
const copy = (link) => navigator.clipboard?.writeText(link);

// ---- Role & permission editor ----
const editableRoles = computed(() => props.roles.filter((r) => r !== props.ownerRole));
const selectedRole = ref(editableRoles.value[0] ?? props.ownerRole);
const editing = ref([...(props.rolePermissions[selectedRole.value] ?? [])]);
watch(selectedRole, (r) => { editing.value = [...(props.rolePermissions[r] ?? [])]; });
const isOwner = computed(() => selectedRole.value === props.ownerRole);
const totalPerms = computed(() => props.permissionGroups.reduce((n, g) => n + g.items.length, 0));

const toggleArea = (group) => {
    const keys = group.items.map((i) => i.key);
    const allOn = keys.every((k) => editing.value.includes(k));
    editing.value = allOn ? editing.value.filter((k) => !keys.includes(k)) : [...new Set([...editing.value, ...keys])];
};
const saveRole = () => router.put(`/roles/${selectedRole.value}/permissions`, { permissions: editing.value }, { preserveScroll: true });

const selStyle = 'h-10 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]';
const columns = [
    { key: 'name', label: 'Member', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'roles', label: 'Roles' },
];
</script>

<template>
    <Head title="Members & Roles" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Members &amp; Roles</h1>
                <p class="mt-1 text-sm text-muted">Add teammates and control what each role can see and do</p>
            </div>
            <Button v-if="canInvite" @click="open = true">Add member</Button>
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

        <!-- Roles & permissions -->
        <Card>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-[15px] font-semibold tracking-[-0.01em] text-ink">Roles &amp; permissions</h3>
                    <p class="mt-1 text-sm text-muted">{{ canManageRoles ? 'Pick a role and choose exactly what it can do.' : 'What each role can do (read-only).' }}</p>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="r in roles" :key="r"
                        :class="['rounded-[var(--radius-control)] px-3 py-1.5 text-[13px] font-medium transition-colors', selectedRole === r ? 'brand-wash text-[var(--brand)] ring-1 ring-[var(--brand)]/30' : 'text-ink-soft ring-1 ring-hairline hover:bg-sunken']"
                        @click="selectedRole = r"
                    >{{ r }}</button>
                </div>
            </div>

            <div v-if="isOwner" class="mt-5 rounded-[var(--radius-control)] border border-hairline bg-sunken/50 p-4 text-sm text-muted">
                <span class="font-medium text-ink">Owner</span> always has full access to everything and can't be restricted.
            </div>

            <div v-else class="mt-5">
                <p class="mb-3 text-xs text-muted"><span class="nums font-medium text-ink">{{ editing.length }}</span> of {{ totalPerms }} permissions enabled for <span class="font-medium text-ink">{{ selectedRole }}</span></p>
                <div class="grid gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="group in permissionGroups" :key="group.area">
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-faint">{{ group.area }}</p>
                            <button v-if="canManageRoles" class="text-[11px] font-medium text-[var(--brand)] hover:underline" @click="toggleArea(group)">Toggle</button>
                        </div>
                        <label v-for="item in group.items" :key="item.key" class="mb-1 flex cursor-pointer items-center gap-2.5 text-sm text-ink-soft">
                            <input type="checkbox" :value="item.key" v-model="editing" :disabled="!canManageRoles" class="size-4 rounded border-hairline accent-[var(--brand)] disabled:opacity-50" />
                            {{ item.label }}
                        </label>
                    </div>
                </div>
                <div v-if="canManageRoles" class="mt-5 flex justify-end border-t border-hairline-soft pt-4">
                    <Button @click="saveRole">Save {{ selectedRole }} permissions</Button>
                </div>
            </div>
        </Card>

        <!-- Add member modal -->
        <Modal :open="open" title="Add a member" @close="open = false">
            <div class="mb-4 flex rounded-[var(--radius-control)] bg-sunken p-0.5 ring-1 ring-hairline">
                <button :class="['flex-1 rounded-[6px] px-2.5 py-1.5 text-[13px] font-medium transition-colors', mode === 'create' ? 'bg-surface text-ink shadow-e1' : 'text-muted']" @click="mode = 'create'">Create account now</button>
                <button :class="['flex-1 rounded-[6px] px-2.5 py-1.5 text-[13px] font-medium transition-colors', mode === 'invite' ? 'bg-surface text-ink shadow-e1' : 'text-muted']" @click="mode = 'invite'">Send invite link</button>
            </div>
            <form class="space-y-4" @submit.prevent="submit">
                <Input v-if="mode === 'create'" v-model="form.name" label="Full name" :error="form.errors.name" />
                <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" />
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Role</label>
                    <select v-model="form.role" :class="selStyle">
                        <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                    </select>
                </div>
                <p class="text-xs text-faint">
                    {{ mode === 'create' ? 'We’ll create the account and email them a temporary password.' : 'We’ll create a shareable invite link they use to set their own password.' }}
                </p>
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">{{ mode === 'create' ? 'Create member' : 'Create invitation' }}</Button>
            </template>
        </Modal>
    </div>
</template>
