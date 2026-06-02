<script setup>
import { ref, watch, onMounted } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button, Kanban, Avatar, Modal, Input } from '@/Components/ui';
import { useTenant } from '@/Composables/useTenant';
import { useEcho } from '@/Composables/useEcho';
import { useToastStore } from '@/Stores/toast';
import { usePermissions } from '@/Composables/usePermissions';

const props = defineProps({ pipeline: Object, pipelines: Array, columns: Array });
const { tenant } = useTenant();
const { can } = usePermissions();
const toast = useToastStore();

// Local reactive board (drag mutates this; Inertia reloads replace it).
const board = ref(structuredClone(props.columns));
watch(() => props.columns, (v) => { board.value = structuredClone(v); });

function onCardMoved({ card, toColumn, index }) {
    router.patch(`/deals/${card.id}/move`, { stage_id: toColumn, board_order: index }, {
        preserveScroll: true, preserveState: true,
        onError: () => { toast.error('Could not move deal'); router.reload({ only: ['columns'] }); },
    });
}

// Live: when another user moves a deal, reflect it on this board.
onMounted(() => {
    const t = tenant.value?.id;
    if (t && props.pipeline) {
        useEcho(`tenant.${t}.pipeline.${props.pipeline.id}`, '.DealStageChanged', (e) => {
            let moved;
            board.value.forEach((col) => {
                const i = col.cards.findIndex((c) => c.id === e.deal_id);
                if (i !== -1) moved = col.cards.splice(i, 1)[0];
            });
            const target = board.value.find((c) => c.id === e.to_stage_id);
            if (moved && target) { target.cards.splice(e.board_order ?? 0, 0, moved); toast.push({ message: `${moved.title} moved`, type: 'info' }); }
        });
    }
});

const open = ref(false);
const form = useForm({ name: '', amount: '', stage_id: props.columns[0]?.id });
const submit = () => form.post('/deals', { preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); router.reload({ only: ['columns'] }); } });
</script>

<template>
    <Head title="Deals" />
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">{{ pipeline?.name ?? 'Deals' }}</h1>
                <p class="mt-1 text-sm text-muted">Drag cards between stages — updates sync live to your team</p>
            </div>
            <Button v-if="can('deals.manage')" @click="open = true">New deal</Button>
        </div>

        <Kanban :columns="board" @card-moved="onCardMoved">
            <template #card="{ card }">
                <p class="text-sm font-medium text-ink">{{ card.title }}</p>
                <p v-if="card.company" class="mt-0.5 text-xs text-muted">{{ card.company }}</p>
                <div class="mt-2.5 flex items-center justify-between">
                    <span class="nums text-[13px] font-medium text-ink">{{ card.amount }}</span>
                    <Avatar v-if="card.owner" :name="card.owner" size="sm" />
                </div>
            </template>
        </Kanban>

        <Modal :open="open" title="New deal" @close="open = false">
            <form class="space-y-4" @submit.prevent="submit">
                <Input v-model="form.name" label="Deal name" :error="form.errors.name" />
                <Input v-model="form.amount" type="number" label="Amount (USD)" placeholder="5000" />
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-muted">Stage</label>
                    <select v-model="form.stage_id" class="h-9 w-full rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
                        <option v-for="c in columns" :key="c.id" :value="c.id">{{ c.title }}</option>
                    </select>
                </div>
            </form>
            <template #footer>
                <Button variant="secondary" @click="open = false">Cancel</Button>
                <Button :loading="form.processing" @click="submit">Create deal</Button>
            </template>
        </Modal>
    </div>
</template>
