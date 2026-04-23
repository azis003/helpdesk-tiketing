<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
    router.get(route('master.users.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));

const form = useForm({});

const toggleActive = (user) => {
    if (confirm(`Apakah Anda yakin ingin mengubah status pengguna ini?`)) {
        form.patch(route('master.users.toggle-active', user.id));
    }
};
</script>

<template>
    <Head title="Master Pengguna" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Master Pengguna</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-4 flex justify-between items-center">
                    <div class="w-1/3">
                        <input type="text" v-model="search" placeholder="Cari nama / username..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <Link :href="route('master.users.create')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                        Tambah Pengguna
                    </Link>
                </div>

                <div v-if="$page.props.errors && $page.props.errors.is_active" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ $page.props.errors.is_active }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 overflow-x-auto">
                        <table class="w-full whitespace-nowrap text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 border-b">
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">Username</th>
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4">Role</th>
                                    <th class="px-6 py-4">Unit Kerja</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ user.id }}</td>
                                    <td class="px-6 py-4 font-semibold">{{ user.username }}</td>
                                    <td class="px-6 py-4">{{ user.name }}</td>
                                    <td class="px-6 py-4">
                                        <span v-for="role in user.roles" :key="role.id" class="inline-block px-2 py-1 bg-gray-200 rounded text-xs mr-1">
                                            {{ role.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ user.work_unit ? user.work_unit.name : '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span v-if="user.is_active" class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Aktif</span>
                                        <span v-else class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">Nonaktif</span>
                                    </td>
                                    <td class="px-6 py-4 flex gap-2">
                                        <Link :href="route('master.users.edit', user.id)" class="text-blue-600 hover:text-blue-800">
                                            Edit
                                        </Link>
                                        <button @click="toggleActive(user)" class="text-yellow-600 hover:text-yellow-800">
                                            Toggle
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada data pengguna.</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="mt-4 flex justify-between items-center" v-if="users.links">
                            <div class="flex gap-1 overflow-x-auto">
                                <template v-for="(link, i) in users.links" :key="i">
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
