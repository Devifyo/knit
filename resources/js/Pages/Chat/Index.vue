<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Card, Button, Avatar } from '@/Components/ui';
import { useTenant } from '@/Composables/useTenant';

const props = defineProps({ messages: Array });
const page = usePage();
const { tenant } = useTenant();

const list = ref([...props.messages]);
const online = ref([]);
const body = ref('');
const me = page.props.auth?.user?.id;
const box = ref(null);

const scroll = () => nextTick(() => { if (box.value) box.value.scrollTop = box.value.scrollHeight; });

const send = () => {
    if (!body.value.trim()) return;
    // Optimistic UI; the server broadcasts to everyone else.
    window.axios.post('/chat', { body: body.value }).then(() => {});
    list.value.push({ id: `tmp-${Date.now()}`, body: body.value, author: page.props.auth.user.name, user_id: me });
    body.value = '';
    scroll();
};

onMounted(() => {
    scroll();
    const id = tenant.value?.id;
    if (id && window.Echo) {
        window.Echo.join(`tenant.${id}.chat`)
            .here((users) => { online.value = users; })
            .joining((u) => online.value.push(u))
            .leaving((u) => { online.value = online.value.filter((x) => x.id !== u.id); })
            .listen('.ChatMessageSent', (e) => {
                if (e.user_id !== me) { list.value.push(e); scroll(); }
            });
    }
});
</script>

<template>
    <Head title="Team chat" />
    <div class="mx-auto flex h-[calc(100dvh-140px)] max-w-3xl flex-col">
        <div class="mb-3 flex items-center justify-between">
            <h1 class="text-xl font-semibold tracking-[-0.02em] text-ink">Team chat</h1>
            <div class="flex items-center gap-2">
                <div class="flex -space-x-2">
                    <Avatar v-for="u in online.slice(0, 5)" :key="u.id" :name="u.name" size="sm" />
                </div>
                <span class="text-xs text-muted">{{ online.length }} online</span>
            </div>
        </div>

        <Card flush class="flex min-h-0 flex-1 flex-col">
            <div ref="box" class="flex-1 space-y-3 overflow-y-auto p-5">
                <div v-for="m in list" :key="m.id" class="flex gap-2.5" :class="m.user_id === me ? 'flex-row-reverse' : ''">
                    <Avatar :name="m.author || '?'" size="sm" />
                    <div :class="['max-w-[70%] rounded-[var(--radius-card)] px-3.5 py-2 text-sm', m.user_id === me ? 'bg-[var(--brand)] text-white' : 'bg-sunken text-ink']">
                        <p v-if="m.user_id !== me" class="mb-0.5 text-[11px] font-medium text-muted">{{ m.author }}</p>
                        <p class="whitespace-pre-wrap">{{ m.body }}</p>
                    </div>
                </div>
            </div>
            <form class="flex items-center gap-2 border-t border-hairline-soft p-3" @submit.prevent="send">
                <input v-model="body" placeholder="Message your team…" class="h-9 flex-1 rounded-[var(--radius-control)] bg-surface px-3 text-sm text-ink ring-1 ring-inset ring-hairline focus:outline-none focus:ring-2 focus:ring-[var(--brand)]" />
                <Button @click="send">Send</Button>
            </form>
        </Card>
    </div>
</template>
