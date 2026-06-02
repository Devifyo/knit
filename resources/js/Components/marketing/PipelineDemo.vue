<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const stages = ['New', 'Qualified', 'Won'];
const deals = ref([
    { id: 1, co: 'Northwind', amt: 42000, who: 'SC', stage: 0 },
    { id: 2, co: 'Vertex', amt: 18500, who: 'MD', stage: 0 },
    { id: 3, co: 'Lumen Labs', amt: 61000, who: 'AO', stage: 1 },
    { id: 4, co: 'Brightwave', amt: 27300, who: 'JL', stage: 1 },
    { id: 5, co: 'Cedar & Co.', amt: 9800, who: 'PR', stage: 2 },
    { id: 6, co: 'Halcyon', amt: 34500, who: 'Tn', stage: 0 },
]);
const inStage = (s) => deals.value.filter((d) => d.stage === s);
const fmt = (n) => '$' + (n / 1000).toFixed(0) + 'K';
const openValue = computed(() => deals.value.filter((d) => d.stage < 2).reduce((a, d) => a + d.amt, 0));

let ptr = 2, timer = null;
const advance = () => { const d = deals.value[ptr % deals.value.length]; d.stage = (d.stage + 1) % 3; ptr++; };
onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    timer = setInterval(advance, 2000);
});
onUnmounted(() => timer && clearInterval(timer));
</script>

<template>
    <div class="rounded-2xl border border-hairline bg-surface p-3.5 shadow-e2">
        <div class="mb-3 flex items-center justify-between px-0.5">
            <p class="text-[12px] font-semibold tracking-[-0.01em] text-ink">Sales Pipeline</p>
            <span class="text-[11px] text-muted">Open <span class="nums inline-block w-[3.25rem] text-right font-medium text-ink-soft">{{ fmt(openValue) }}</span></span>
        </div>
        <div class="grid h-[188px] grid-cols-3 gap-2">
            <div v-for="(label, s) in stages" :key="label" class="overflow-hidden rounded-xl bg-sunken/60 p-1.5">
                <div class="mb-1.5 flex items-center justify-between px-1 text-[8px] font-semibold uppercase tracking-wide text-faint">
                    {{ label }} <span class="rounded-full bg-surface px-1 ring-1 ring-hairline">{{ inStage(s).length }}</span>
                </div>
                <TransitionGroup tag="div" name="flow" class="relative space-y-1.5">
                    <div v-for="d in inStage(s)" :key="d.id"
                         :class="['rounded-lg border bg-surface p-1.5 shadow-e1', s === 2 ? 'border-positive/30' : 'border-hairline']">
                        <p class="truncate text-[10px] font-semibold tracking-[-0.01em] text-ink">{{ d.co }}</p>
                        <div class="mt-1 flex items-center justify-between">
                            <span class="nums text-[10px] font-medium" :class="s === 2 ? 'text-positive' : 'text-muted'">{{ fmt(d.amt) }}</span>
                            <span class="grid size-3.5 place-items-center rounded-full bg-sunken text-[7px] font-semibold text-ink-soft ring-1 ring-hairline">{{ d.who }}</span>
                        </div>
                    </div>
                </TransitionGroup>
            </div>
        </div>
    </div>
</template>

<style scoped>
.flow-move { transition: transform 0.55s cubic-bezier(0.2,0.7,0.3,1); }
.flow-enter-active { transition: opacity 0.45s ease, transform 0.45s cubic-bezier(0.2,0.7,0.3,1); }
.flow-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; position: absolute; left: 0; right: 0; }
.flow-enter-from { opacity: 0; transform: translateX(-12px) scale(0.95); }
.flow-leave-to { opacity: 0; transform: translateX(12px) scale(0.95); }
@media (prefers-reduced-motion: reduce) {
    .flow-move, .flow-enter-active, .flow-leave-active { transition: none; }
}
</style>
