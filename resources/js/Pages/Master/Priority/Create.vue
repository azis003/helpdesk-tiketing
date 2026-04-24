<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';

const form = useForm({
    name: '',
    level: 1,
    color: '#000000',
});

const submit = () => {
    form.post(route('master.priorities.store'));
};
</script>

<template>
    <Head title="Tambah Prioritas" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Prioritas</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="name" class="block font-medium text-sm text-gray-700">Nama Prioritas</label>
                                <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.name" required autofocus />
                                <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="level" class="block font-medium text-sm text-gray-700">Level (Angka unik, min 1)</label>
                                <input id="level" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.level" required />
                                <div v-if="form.errors.level" class="text-sm text-red-600 mt-1">{{ form.errors.level }}</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="color" class="block font-medium text-sm text-gray-700">Warna Hex (Opsional)</label>
                                <div class="flex items-center gap-2 mt-1">
                                    <input id="color" type="color" class="h-10 w-10 border-0 p-0 rounded-md shadow-sm" v-model="form.color" />
                                    <input type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.color" placeholder="#000000" pattern="^#[0-9A-Fa-f]{6}$" />
                                </div>
                                <div v-if="form.errors.color" class="text-sm text-red-600 mt-1">{{ form.errors.color }}</div>
                            </div>

                            <div class="flex items-center justify-end mt-4 gap-4">
                                <Link :href="route('master.priorities.index')" class="text-gray-600 hover:text-gray-900 underline">
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
