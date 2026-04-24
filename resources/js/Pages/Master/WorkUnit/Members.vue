<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';

const props = defineProps({
    workUnit: Object,
    users: Array,
});

const form = useForm({
    user_id: '',
});

const addMember = () => {
    form.post(route('master.work-units.members.store', props.workUnit.id), {
        onSuccess: () => form.reset('user_id'),
    });
};

const removeMember = (userId) => {
    if (confirm('Apakah Anda yakin ingin menghapus anggota ini dari unit kerja?')) {
        const deleteForm = useForm({});
        deleteForm.delete(route('master.work-units.members.destroy', [props.workUnit.id, userId]));
    }
};
</script>

<template>
    <Head title="Anggota Unit Kerja" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Anggota Tim: {{ workUnit.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Tambah Anggota</h3>
                        <form @submit.prevent="addMember" class="flex gap-4 items-end">
                            <div class="flex-1">
                                <label for="user" class="block font-medium text-sm text-gray-700">Pilih Pengguna</label>
                                <select id="user" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.user_id" required>
                                    <option value="" disabled>-- Pilih Pengguna --</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }} ({{ user.username }})
                                    </option>
                                </select>
                                <div v-if="form.errors.user_id" class="text-sm text-red-600 mt-1">{{ form.errors.user_id }}</div>
                            </div>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition h-[42px]" :disabled="form.processing">
                                Tambah
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 overflow-x-auto">
                        <table class="w-full whitespace-nowrap text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 border-b">
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">Username</th>
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="member in workUnit.members" :key="member.id" class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ member.id }}</td>
                                    <td class="px-6 py-4">{{ member.username }}</td>
                                    <td class="px-6 py-4">{{ member.name }}</td>
                                    <td class="px-6 py-4">
                                        <button @click="removeMember(member.id)" class="text-red-600 hover:text-red-800">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!workUnit.members || workUnit.members.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada anggota.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-start">
                    <Link :href="route('master.work-units.index')" class="text-gray-600 hover:text-gray-900 underline">
                        &larr; Kembali ke daftar Unit Kerja
                    </Link>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
