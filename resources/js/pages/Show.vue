<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@statamic/cms/inertia';
import { Header, CardPanel, Button, Icon, Switch, ConfirmationModal } from '@statamic/cms/ui';
import QRCode from 'qrcode';

const props = defineProps({
    url: { type: Object, required: true },
    visits: { type: Array, required: true },
    canEdit: { type: Boolean, default: false },
});

const copied = ref(false);
const showDeleteDialog = ref(false);
const excludeBots = ref(false);
const qrDataUrl = ref(null);

const filteredVisits = computed(() => {
    return excludeBots.value ? props.visits.filter(v => !v.potentialBot) : props.visits;
});

onMounted(async () => {
    qrDataUrl.value = await QRCode.toDataURL(props.url.shortUrl, { width: 256, margin: 2 });
});

function copyToClipboard() {
    navigator.clipboard.writeText(props.url.shortUrl).catch(() => {});
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
}

function deleteUrl() {
    router.delete(cp_url(`shlink-manager/${props.url.shortCode}`));
}
</script>

<template>
    <Head :title="url.shortCode" />

    <div class="max-w-5xl mx-auto">
        <Header :title="url.shortUrl">
            <template #actions>
                <div class="flex items-center gap-2">
                    <Button @click="copyToClipboard" size="sm">
                        <Icon v-if="copied" name="check-circle" class="size-4 text-green-500" />
                        <Icon v-else name="clipboard" class="size-4" />
                        <span class="ms-1">{{ copied ? __('shlink-manager::messages.copied') : __('shlink-manager::messages.copy') }}</span>
                    </Button>
                    <Button v-if="canEdit" :href="cp_url(`shlink-manager/${url.shortCode}/edit`)" size="sm" :text="__('shlink-manager::messages.edit')" />
                    <Button v-if="canEdit" @click="showDeleteDialog = true" size="sm" variant="danger" :text="__('shlink-manager::messages.delete')" />
                </div>
            </template>
        </Header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <CardPanel class="lg:col-span-2">
                <div class="p-6 space-y-4">
                    <h3 class="font-medium text-lg">{{ __('shlink-manager::messages.details') }}</h3>

                    <dl class="space-y-3 text-sm">
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 text-gray-500 dark:text-gray-400">{{ __('shlink-manager::messages.short_url') }}</dt>
                            <dd class="font-mono text-blue-600 dark:text-blue-400">{{ url.shortUrl }}</dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 text-gray-500 dark:text-gray-400">{{ __('shlink-manager::messages.long_url') }}</dt>
                            <dd class="break-all">
                                <a :href="url.longUrl" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">{{ url.longUrl }}</a>
                            </dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 text-gray-500 dark:text-gray-400">{{ __('shlink-manager::messages.visits') }}</dt>
                            <dd class="tabular-nums">{{ url.visitsSummary.total }}</dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 text-gray-500 dark:text-gray-400">{{ __('shlink-manager::messages.created_at') }}</dt>
                            <dd>{{ url.dateCreated }}</dd>
                        </div>
                        <div v-if="url.tags && url.tags.length" class="flex gap-4">
                            <dt class="w-32 shrink-0 text-gray-500 dark:text-gray-400">{{ __('shlink-manager::messages.tags') }}</dt>
                            <dd class="flex gap-1 flex-wrap">
                                <span v-for="tag in url.tags" :key="tag" class="inline-block rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-xs">
                                    {{ tag }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </CardPanel>

            <CardPanel>
                <div class="p-6 text-center">
                    <h3 class="font-medium mb-4">{{ __('shlink-manager::messages.qr_code') }}</h3>
                    <img v-if="qrDataUrl" :src="qrDataUrl" :alt="`QR Code: ${url.shortUrl}`" class="mx-auto w-48 h-48 rounded-lg" />
                    <div v-else class="mx-auto w-48 h-48 rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse" />
                    <a v-if="qrDataUrl" :href="qrDataUrl" download="qr-code.png" class="mt-3 inline-block text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('shlink-manager::messages.download') }}</a>
                </div>
            </CardPanel>
        </div>

        <CardPanel>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-lg">{{ __('shlink-manager::messages.recent_visits') }}</h3>
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <Switch v-model="excludeBots" />
                        {{ __('shlink-manager::messages.exclude_bots') }}
                    </label>
                </div>

                <div v-if="filteredVisits.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                    {{ __('shlink-manager::messages.no_visits') }}
                </div>

                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b dark:border-gray-800">
                            <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">{{ __('shlink-manager::messages.date') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">{{ __('shlink-manager::messages.referer') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">{{ __('shlink-manager::messages.country') }}</th>
                            <th class="px-4 py-2 text-center font-medium text-gray-600 dark:text-gray-400">{{ __('shlink-manager::messages.bot') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(visit, i) in filteredVisits" :key="i" class="border-b dark:border-gray-800">
                            <td class="px-4 py-2 tabular-nums">{{ visit.date }}</td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-400 truncate max-w-xs">{{ visit.referer || '-' }}</td>
                            <td class="px-4 py-2">{{ visit.location?.countryName || '-' }}</td>
                            <td class="px-4 py-2 text-center">
                                <span v-if="visit.potentialBot" class="text-yellow-500">{{ __('shlink-manager::messages.bot') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardPanel>

        <ConfirmationModal
            v-model:open="showDeleteDialog"
            :title="__('shlink-manager::messages.confirm_delete')"
            :danger="true"
            @confirm="deleteUrl"
        />
    </div>
</template>
