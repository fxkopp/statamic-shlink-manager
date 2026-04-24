<script setup>
import { ref } from 'vue';
import { Head, router } from '@statamic/cms/inertia';
import { Header } from '@statamic/cms/ui';
import ShortUrlForm from '../components/ShortUrlForm.vue';

defineProps({
    domain: { type: String, required: true },
    existingTags: { type: Array, default: () => [] },
});

const form = ref({
    long_url: '',
    custom_slug: '',
    tags: [],
    title: '',
    valid_since: '',
    valid_until: '',
    max_visits: '',
    crawlable: true,
    forward_query: true,
});

const saving = ref(false);

function submit() {
    saving.value = true;
    router.post(cp_url('shlink-manager'), {
        ...form.value,
        max_visits: form.value.max_visits || null,
        custom_slug: form.value.custom_slug || null,
        title: form.value.title || null,
        valid_since: form.value.valid_since || null,
        valid_until: form.value.valid_until || null,
    }, {
        onFinish: () => saving.value = false,
    });
}
</script>

<template>
    <Head :title="__('shlink-manager::messages.create')" />

    <div class="max-w-lg mx-auto">
        <Header :title="__('shlink-manager::messages.create')" />

        <ShortUrlForm
            v-model="form"
            :domain="domain"
            :existingTags="existingTags"
            :saving="saving"
            :submitLabel="__('shlink-manager::messages.save')"
            @submit="submit"
        />
    </div>
</template>
