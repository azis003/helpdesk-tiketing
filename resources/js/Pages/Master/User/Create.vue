<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';

const props = defineProps({
    roles: Array,
    workUnits: Array,
});

const form = useForm({
    username: '',
    name: '',
    email: '',
    password: '',
    roles: [],
    work_unit_id: '',
});

const getRolesError = () => {
    if (form.errors.roles) {
        return form.errors.roles;
    }

    const nestedRoleError = Object.entries(form.errors).find(([field]) => field.startsWith('roles.'));
    return nestedRoleError ? nestedRoleError[1] : null;
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        work_unit_id: data.work_unit_id === '' ? null : data.work_unit_id,
    })).post(route('master.users.store'));
};
</script>

<template>
    <Head title="Tambah Pengguna" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Pengguna</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="mb-4">
                                    <label for="username" class="block font-medium text-sm text-gray-700">Username</label>
                                    <input id="username" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.username" required autofocus />
                                    <div v-if="form.errors.username" class="text-sm text-red-600 mt-1">{{ form.errors.username }}</div>
                                </div>

                                <div class="mb-4">
                                    <label for="name" class="block font-medium text-sm text-gray-700">Nama Lengkap</label>
                                    <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.name" required />
                                    <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                                </div>

                                <div class="mb-4">
                                    <label for="email" class="block font-medium text-sm text-gray-700">Email (Opsional)</label>
                                    <input id="email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.email" />
                                    <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
                                    <input id="password" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.password" required />
                                    <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</div>
                                </div>

                                <div class="mb-4">
                                    <label for="roles" class="block font-medium text-sm text-gray-700">Role</label>
                                    <select id="roles" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 min-h-28" v-model="form.roles" required>
                                        <option v-for="role in roles" :key="role.id" :value="role.name">
                                            {{ role.name }}
                                        </option>
                                    </select>
                                    <span class="text-xs text-gray-500">Pilih satu atau lebih role.</span>
                                    <div v-if="getRolesError()" class="text-sm text-red-600 mt-1">{{ getRolesError() }}</div>
                                </div>

                                <div class="mb-4">
                                    <label for="work_unit_id" class="block font-medium text-sm text-gray-700">Unit Kerja (Opsional)</label>
                                    <select id="work_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.work_unit_id">
                                        <option value="">-- Tidak ada --</option>
                                        <option v-for="unit in workUnits" :key="unit.id" :value="unit.id">
                                            {{ unit.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.work_unit_id" class="text-sm text-red-600 mt-1">{{ form.errors.work_unit_id }}</div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4 gap-4">
                                <Link :href="route('master.users.index')" class="text-gray-600 hover:text-gray-900 underline">
                                    Batal
                                </Link>
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition" :disabled="form.processing">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
