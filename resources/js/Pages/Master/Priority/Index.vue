<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';

defineProps({
    priorities: Object,
});

const form = useForm({});

const toggleActive = (priority) => {
    if (confirm(`Apakah Anda yakin ingin mengubah status prioritas ini?`)) {
        form.patch(route('master.priorities.toggle-active', priority.id));
    }
};
</script>

<template>
    <Head title="Master Prioritas" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Master Prioritas</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-4 flex justify-end">
                    <Link :href="route('master.priorities.create')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                        Tambah Prioritas
                    </Link>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 overflow-x-auto">
                        <table class="w-full whitespace-nowrap text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 border-b">
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4">Level</th>
                                    <th class="px-6 py-4">Warna</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="priority in priorities.data" :key="priority.id" class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ priority.id }}</td>
                                    <td class="px-6 py-4 font-medium">{{ priority.name }}</td>
                                    <td class="px-6 py-4">{{ priority.level }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded border" :style="{ backgroundColor: priority.color || '#ccc' }"></div>
                                            <span class="text-xs text-gray-500">{{ priority.color || '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="priority.is_active" class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Aktif</span>
                                        <span v-else class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">Nonaktif</span>
                                    </td>
                                    <td class="px-6 py-4 flex gap-2">
                                        <Link :href="route('master.priorities.edit', priority.id)" class="text-blue-600 hover:text-blue-800">
                                            Edit
                                        </Link>
                                        <button @click="toggleActive(priority)" class="text-yellow-600 hover:text-yellow-800">
                                            Toggle
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="priorities.data.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="mt-4 flex justify-between items-center" v-if="priorities.links">
                            <div class="flex gap-1">
                                <template v-for="(link, i) in priorities.links" :key="i">
                                    <Link v-if="link.url" :href="link.url" class="px-3 py-1 border rounded-md" :class="{'bg-blue-600 text-white': link.active}" v-html="link.label"></Link>
                                    <span v-else class="px-3 py-1 border rounded-md text-gray-400" v-html="link.label"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
