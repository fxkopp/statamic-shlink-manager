<script setup>
import { ref } from 'vue';
import { CardPanel, Button, Input, Combobox, Switch } from '@statamic/cms/ui';

const props = defineProps({
    modelValue: { type: Object, required: true },
    domain: { type: String, required: true },
    existingTags: { type: Array, default: () => [] },
    showSlug: { type: Boolean, default: true },
    saving: { type: Boolean, default: false },
    submitLabel: { type: String, default: 'Save' },
});

const emit = defineEmits(['update:modelValue', 'submit']);

const showAdvanced = ref(
    !!(props.modelValue.title || props.modelValue.valid_since || props.modelValue.valid_until || props.modelValue.max_visits)
);

const tagOptions = props.existingTags.map(t => ({ label: t, value: t }));

function update(key, value) {
    emit('update:modelValue', { ...props.modelValue, [key]: value });
}
</script>

<template>
    <CardPanel>
        <form @submit.prevent="$emit('submit')" class="p-6 space-y-6">
            <div>
                <label for="long-url" class="block text-sm font-medium mb-1.5">{{ __('shlink-manager::messages.long_url') }} *</label>
                <Input
                    id="long-url"
                    :modelValue="modelValue.long_url"
                    @update:modelValue="update('long_url', $event)"
                    type="url"
                    placeholder="https://example.com/my-page"
                    required
                />
            </div>

            <div v-if="showSlug">
                <label for="custom-slug" class="block text-sm font-medium mb-1.5">{{ __('shlink-manager::messages.custom_slug') }}</label>
                <div class="flex items-center gap-0">
                    <span class="shrink-0 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400">
                        {{ domain }}/
                    </span>
                    <Input
                        id="custom-slug"
                        :modelValue="modelValue.custom_slug"
                        @update:modelValue="update('custom_slug', $event)"
                        placeholder="my-link"
                        class="!rounded-l-none"
                    />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('shlink-manager::messages.slug_help') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1.5">{{ __('shlink-manager::messages.tags') }}</label>
                <Combobox
                    :modelValue="modelValue.tags"
                    @update:modelValue="update('tags', $event)"
                    :options="tagOptions"
                    :multiple="true"
                    :taggable="true"
                    :placeholder="__('shlink-manager::messages.tags_placeholder')"
                />
            </div>

            <div>
                <button type="button" @click="showAdvanced = !showAdvanced" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    {{ showAdvanced ? __('shlink-manager::messages.hide_advanced') : __('shlink-manager::messages.show_advanced') }}
                </button>
            </div>

            <template v-if="showAdvanced">
                <div>
                    <label for="title" class="block text-sm font-medium mb-1.5">{{ __('shlink-manager::messages.url_title') }}</label>
                    <Input
                        id="title"
                        :modelValue="modelValue.title"
                        @update:modelValue="update('title', $event)"
                        :placeholder="__('shlink-manager::messages.title_placeholder')"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="valid-since" class="block text-sm font-medium mb-1.5">{{ __('shlink-manager::messages.valid_since') }}</label>
                        <Input
                            id="valid-since"
                            type="date"
                            :modelValue="modelValue.valid_since"
                            @update:modelValue="update('valid_since', $event)"
                        />
                    </div>
                    <div>
                        <label for="valid-until" class="block text-sm font-medium mb-1.5">{{ __('shlink-manager::messages.valid_until') }}</label>
                        <Input
                            id="valid-until"
                            type="date"
                            :modelValue="modelValue.valid_until"
                            @update:modelValue="update('valid_until', $event)"
                        />
                    </div>
                </div>

                <div>
                    <label for="max-visits" class="block text-sm font-medium mb-1.5">{{ __('shlink-manager::messages.max_visits') }}</label>
                    <Input
                        id="max-visits"
                        type="number"
                        min="1"
                        :modelValue="modelValue.max_visits"
                        @update:modelValue="update('max_visits', $event)"
                        :placeholder="__('shlink-manager::messages.max_visits_placeholder')"
                    />
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 text-sm">
                        <Switch
                            :modelValue="modelValue.crawlable"
                            @update:modelValue="update('crawlable', $event)"
                        />
                        {{ __('shlink-manager::messages.crawlable') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <Switch
                            :modelValue="modelValue.forward_query"
                            @update:modelValue="update('forward_query', $event)"
                        />
                        {{ __('shlink-manager::messages.forward_query') }}
                    </label>
                </div>
            </template>

            <div class="flex items-center justify-end gap-3 pt-2">
                <Button :href="cp_url('shlink-manager')" :text="__('shlink-manager::messages.cancel')" />
                <Button type="submit" variant="primary" :text="submitLabel" :disabled="saving" />
            </div>
        </form>
    </CardPanel>
</template>
