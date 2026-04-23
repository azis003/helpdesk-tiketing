<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';

const props = defineProps({
    roles: Array,
    permissions: Array,
});

// Construct initial form state
// format: { roleId: [permissionId1, permissionId2, ...] }
const initialState = {};
props.roles.forEach(role => {
    initialState[role.id] = role.permissions.map(p => p.id);
});

const form = useForm({
    permissions: initialState
});

const submit = () => {
    form.post(route('master.permissions.update'));
};
</script>

<template>
    <Head title="Kelola Permission" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Permission per Role</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 text-gray-900">
                        
                        <div class="mb-4 flex justify-between items-center">
                            <p class="text-sm text-gray-600">Centang kotak untuk memberikan hak akses kepada role tertentu.</p>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition" :disabled="form.processing">
                                Simpan Perubahan
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full whitespace-nowrap text-left border-collapse border border-gray-200">
                                <thead>
                                    <tr class="bg-gray-100 border-b">
                                        <th class="px-4 py-3 border border-gray-200 w-1/4">Nama Permission</th>
                                        <th v-for="role in roles" :key="role.id" class="px-4 py-3 border border-gray-200 text-center font-medium">
                                            {{ role.name }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="permission in permissions" :key="permission.id" class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 border border-gray-200 text-sm font-semibold text-gray-700">
                                            {{ permission.name }}
                                        </td>
                                        <td v-for="role in roles" :key="role.id" class="px-4 py-3 border border-gray-200 text-center">
                                            <input 
                                                type="checkbox" 
                                                :value="permission.id" 
                                                v-model="form.permissions[role.id]" 
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-5 h-5 cursor-pointer"
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition font-medium" :disabled="form.processing">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
