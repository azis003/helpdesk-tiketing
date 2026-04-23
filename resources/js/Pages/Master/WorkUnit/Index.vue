<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';

defineProps({
    workUnits: Object,
});

const form = useForm({});

const toggleActive = (workUnit) => {
    if (confirm(`Apakah Anda yakin ingin mengubah status unit kerja ini?`)) {
        form.patch(route('master.work-units.toggle-active', workUnit.id));
    }
};
</script>

<template>
    <Head title="Master Unit Kerja" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Master Unit Kerja</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-4 flex justify-end">
                    <Link :href="route('master.work-units.create')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                        Tambah Unit Kerja
                    </Link>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 overflow-x-auto">
                        <table class="w-full whitespace-nowrap text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 border-b">
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">Kode</th>
                                    <th class="px-6 py-4">Nama Unit Kerja</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="unit in workUnits.data" :key="unit.id" class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ unit.id }}</td>
                                    <td class="px-6 py-4 font-semibold">{{ unit.code }}</td>
                                    <td class="px-6 py-4 font-medium">{{ unit.name }}</td>
                                    <td class="px-6 py-4">
                                        <span v-if="unit.is_active" class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Aktif</span>
                                        <span v-else class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">Nonaktif</span>
                                    </td>
                                    <td class="px-6 py-4 flex gap-2">
                                        <Link :href="route('master.work-units.members', unit.id)" class="text-indigo-600 hover:text-indigo-800 mr-2">
                                            Anggota Tim
                                        </Link>
                                        <Link :href="route('master.work-units.edit', unit.id)" class="text-blue-600 hover:text-blue-800">
                                            Edit
                                        </Link>
                                        <button @click="toggleActive(unit)" class="text-yellow-600 hover:text-yellow-800">
                                            Toggle
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="workUnits.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="mt-4 flex justify-between items-center" v-if="workUnits.links">
                            <div class="flex gap-1">
                                <template v-for="(link, i) in workUnits.links" :key="i">
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
