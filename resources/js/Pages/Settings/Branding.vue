<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Button, Card, Input } from '@/Components/ui';

defineOptions({ layout: SettingsLayout });
const props = defineProps({ branding: Object });

const form = useForm({
    _method: 'put',
    name: props.branding.name,
    brand_color: props.branding.brand_color,
    ai_enabled: props.branding.ai_enabled,
    logo: null,
});

// Live preview: push the chosen color straight to the CSS variable.
const preview = () => document.documentElement.style.setProperty('--brand', form.brand_color);

const submit = () => form.post('/settings/branding', { preserveScroll: true, forceFormData: true });
</script>

<template>
    <Head title="Branding" />
    <Card title="White-label branding" subtitle="Changes apply live across the workspace">
        <form class="max-w-lg space-y-4" @submit.prevent="submit">
            <Input v-model="form.name" label="Workspace name" :error="form.errors.name" />

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Brand color</label>
                <div class="flex items-center gap-3">
                    <input v-model="form.brand_color" type="color" class="h-10 w-14 rounded border border-gray-300" @input="preview" />
                    <Input v-model="form.brand_color" class="flex-1" :error="form.errors.brand_color" @update:model-value="preview" />
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Logo</label>
                <input type="file" accept="image/*" class="text-sm" @input="form.logo = $event.target.files[0]" />
                <p v-if="form.errors.logo" class="mt-1 text-xs text-red-600">{{ form.errors.logo }}</p>
            </div>

            <label class="flex items-center gap-2 border-t border-hairline-soft pt-4 text-sm text-ink-soft">
                <input v-model="form.ai_enabled" type="checkbox" class="rounded border-hairline text-[var(--brand)]" />
                Enable AI features (Gemini) for this workspace
            </label>

            <div class="flex items-center gap-3">
                <Button type="submit" :loading="form.processing">Save settings</Button>
                <span class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-white" :style="{ background: form.brand_color }">
                    Live preview
                </span>
            </div>
        </form>
    </Card>
</template>
