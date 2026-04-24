<script setup>
import { ref } from 'vue';
import { Head, router } from '@statamic/cms/inertia';
import { Header } from '@statamic/cms/ui';
import ShortUrlForm from '../components/ShortUrlForm.vue';

const props = defineProps({
    url: { type: Object, required: true },
    domain: { type: String, required: true },
    existingTags: { type: Array, default: () => [] },
});

const form = ref({
    long_url: props.url.longUrl,
    tags: props.url.tags || [],
    title: props.url.title || '',
    valid_since: props.url.validSince || '',
    valid_until: props.url.validUntil || '',
    max_visits: props.url.maxVisits || '',
    crawlable: props.url.crawlable ?? true,
    forward_query: props.url.forwardQuery ?? true,
});

const saving = ref(false);

function submit() {
    saving.value = true;
    router.patch(cp_url(`shlink-manager/${props.url.shortCode}`), {
        ...form.value,
        max_visits: form.value.max_visits || null,
        title: form.value.title || null,
        valid_since: form.value.valid_since || null,
        valid_until: form.value.valid_until || null,
    }, {
        onFinish: () => saving.value = false,
    });
}
</script>

<template>
    <Head :title="`${__('shlink-manager::messages.edit')} — ${url.shortCode}`" />

    <div class="max-w-lg mx-auto">
        <Header :title="`${__('shlink-manager::messages.edit')}: ${domain}/${url.shortCode}`" />

        <ShortUrlForm
            v-model="form"
            :domain="domain"
            :existingTags="existingTags"
            :showSlug="false"
            :saving="saving"
            :submitLabel="__('shlink-manager::messages.save')"
            @submit="submit"
        />
    </div>
</template>
