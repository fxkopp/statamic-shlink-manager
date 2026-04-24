<script setup>
import { Widget, Button } from '@statamic/cms/ui';
import { truncate } from '../utils/truncate';

defineProps({
    urls: { type: Array, required: true },
    totalVisits: { type: Number, default: 0 },
    indexUrl: { type: String, required: true },
    createUrl: { type: String, required: true },
});
</script>

<template>
    <Widget :title="__('shlink-manager::messages.recent_links')">
        <template #actions>
            <Button :href="indexUrl" size="sm" :text="__('shlink-manager::messages.view_all')" />
        </template>

        <div v-if="urls.length === 0" class="flex flex-col items-center justify-center gap-4" style="min-height: 159px">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('shlink-manager::messages.no_urls') }}</p>
            <Button :href="createUrl" variant="primary" :text="__('shlink-manager::messages.create')" />
        </div>

        <div v-else>
            <div class="mb-3 text-center">
                <span class="text-3xl font-light tabular-nums">{{ totalVisits }}</span>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('shlink-manager::messages.total_visits') }}</p>
            </div>

            <ul class="divide-y dark:divide-gray-800">
                <li v-for="url in urls" :key="url.shortCode">
                    <a :href="`${indexUrl}/${url.shortCode}`" class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-900 rounded">
                        <div class="min-w-0">
                            <span class="block font-mono text-sm text-blue-600 dark:text-blue-400">{{ url.shortCode }}</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 truncate">{{ truncate(url.longUrl, 40) }}</span>
                        </div>
                        <span class="shrink-0 ml-3 tabular-nums text-sm text-gray-600 dark:text-gray-400">{{ url.visitsSummary.total }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </Widget>
</template>
