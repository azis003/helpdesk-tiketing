<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links && links.length > 3" class="flex flex-wrap -mb-1">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded-xl"
                v-html="link.label"
            />
            <Link
                v-else
                class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded-xl hover:bg-white hover:text-blue-600 focus:border-blue-500 focus:text-blue-500 transition-all duration-200"
                :class="{ 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-600/20': link.active, 'bg-white text-gray-700 border-gray-200': !link.active }"
                :href="link.url"
                v-html="link.label"
            />
        </template>
    </div>
</template>
