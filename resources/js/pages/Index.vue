<script setup>
import { ref } from 'vue';
import { Link, Head, router } from '@statamic/cms/inertia';
import { Header, CardPanel, Button, Input, Icon, Badge, ConfirmationModal, Dropdown, DropdownMenu, DropdownItem, DropdownSeparator } from '@statamic/cms/ui';
import { truncate } from '../utils/truncate';

function urlStatus(url) {
    if (url.validUntil && new Date(url.validUntil) < new Date()) return 'expired';
    if (url.maxVisits && url.visitsSummary.total >= url.maxVisits) return 'max_reached';
    if (url.validSince && new Date(url.validSince) > new Date()) return 'scheduled';
    return null;
}

const props = defineProps({
    urls: { type: Array, required: true },
    overview: { type: Object, required: true },
    search: { type: String, default: '' },
    domain: { type: String, required: true },
    canEdit: { type: Boolean, default: false },
    isSuper: { type: Boolean, default: false },
});

const searchQuery = ref(props.search || '');
const deletingUrl = ref(null);
const deleteDialogOpen = ref(false);
const copiedCode = ref(null);

function openShlinkWeb() {
    window.open('https://app.shlink.io', '_blank', 'noopener');
}

function handleSearch() {
    router.get(cp_url('shlink-manager'), { search: searchQuery.value || undefined }, { preserveState: true });
}

function copyToClipboard(shortUrl) {
    navigator.clipboard.writeText(shortUrl).catch(() => {});
    copiedCode.value = shortUrl;
    setTimeout(() => copiedCode.value = null, 2000);
}

function confirmDelete(shortCode) {
    deletingUrl.value = shortCode;
    deleteDialogOpen.value = true;
}

function deleteUrl() {
    router.delete(cp_url(`shlink-manager/${deletingUrl.value}`), {
        onFinish: () => {
            deletingUrl.value = null;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <Head :title="__('shlink-manager::messages.title')" />

    <div class="max-w-5xl mx-auto">
        <Header :title="__('shlink-manager::messages.title')">
            <template #actions>
                <div class="flex items-center gap-2">
                    <Dropdown v-if="isSuper">
                        <template #trigger>
                            <Button size="sm" :text="__('shlink-manager::messages.shlink_web')" />
                        </template>
                        <DropdownMenu>
                            <DropdownItem @click="openShlinkWeb" :text="__('shlink-manager::messages.open_shlink_web')" />
                            <DropdownSeparator />
                            <DropdownItem :href="cp_url('shlink-manager/servers-csv')" :text="__('shlink-manager::messages.download_csv')" />
                        </DropdownMenu>
                    </Dropdown>
                    <Button v-if="canEdit" :href="cp_url('shlink-manager/create')" variant="primary" :text="__('shlink-manager::messages.create')" />
                </div>
            </template>
        </Header>

        <div class="mb-6 flex items-center gap-6">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <Icon name="chart-monitoring-indicator" class="size-4" />
                <span>{{ overview.totalVisits }} {{ __('shlink-manager::messages.total_visits') }}</span>
            </div>
        </div>

        <CardPanel>
            <div class="p-4 border-b dark:border-gray-800">
                <form @submit.prevent="handleSearch">
                    <label for="shlink-search" class="sr-only">{{ __('shlink-manager::messages.search_placeholder') }}</label>
                    <Input
                        id="shlink-search"
                        v-model="searchQuery"
                        :placeholder="__('shlink-manager::messages.search_placeholder')"
                    />
                </form>
            </div>

            <div v-if="urls.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                {{ __('shlink-manager::messages.no_urls') }}
            </div>

            <table v-else class="w-full text-sm table-fixed">
                <thead>
                    <tr class="border-b dark:border-gray-800">
                        <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400 w-1/4">{{ __('shlink-manager::messages.short_url') }}</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400 w-2/5">{{ __('shlink-manager::messages.long_url') }}</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600 dark:text-gray-400">{{ __('shlink-manager::messages.visits') }}</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600 dark:text-gray-400">{{ __('shlink-manager::messages.created_at') }}</th>
                        <th class="px-4 py-2"><span class="sr-only">{{ __('shlink-manager::messages.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="url in urls" :key="url.shortCode" class="border-b dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900">
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                <Link :href="cp_url(`shlink-manager/${url.shortCode}`)" class="font-mono text-blue-600 dark:text-blue-400 hover:underline" :title="url.shortUrl">
                                    /{{ url.shortCode }}
                                </Link>
                                <Badge v-if="urlStatus(url) === 'expired'" color="red">{{ __('shlink-manager::messages.expired') }}</Badge>
                                <Badge v-else-if="urlStatus(url) === 'max_reached'" color="yellow">{{ __('shlink-manager::messages.max_reached') }}</Badge>
                                <Badge v-else-if="urlStatus(url) === 'scheduled'" color="blue">{{ __('shlink-manager::messages.scheduled') }}</Badge>
                                <button
                                    @click.stop="copyToClipboard(url.shortUrl)"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                    :aria-label="__('shlink-manager::messages.copy')"
                                >
                                    <Icon v-if="copiedCode === url.shortUrl" name="check-circle" class="size-4 text-green-500" />
                                    <Icon v-else name="clipboard" class="size-4" />
                                </button>
                            </div>
                            <div v-if="url.tags && url.tags.length" class="mt-1 flex gap-1">
                                <span v-for="tag in url.tags" :key="tag" class="inline-block rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs text-gray-600 dark:text-gray-400">
                                    {{ tag }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 truncate" :title="url.longUrl">
                            {{ url.longUrl }}
                        </td>
                        <td class="px-4 py-2.5 text-right tabular-nums">
                            {{ url.visitsSummary.total }}
                        </td>
                        <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-400">
                            {{ url.dateCreated }}
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            <button v-if="canEdit" @click.stop="confirmDelete(url.shortCode)" class="text-gray-400 hover:text-red-500" :aria-label="__('shlink-manager::messages.delete')">
                                <Icon name="trash" class="size-4" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </CardPanel>

        <ConfirmationModal
            v-model:open="deleteDialogOpen"
            :title="__('shlink-manager::messages.confirm_delete')"
            :danger="true"
            @confirm="deleteUrl"
        />
    </div>
</template>
