<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';

const form = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post(route('master.categories.store'));
};
</script>

<template>
    <Head title="Tambah Kategori" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Kategori</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="name" class="block font-medium text-sm text-gray-700">Nama Kategori</label>
                                <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.name" required autofocus />
                                <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="block font-medium text-sm text-gray-700">Deskripsi (Opsional)</label>
                                <textarea id="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.description" rows="3"></textarea>
                                <div v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</div>
                            </div>

                            <div class="flex items-center justify-end mt-4 gap-4">
                                <Link :href="route('master.categories.index')" class="text-gray-600 hover:text-gray-900 underline">
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
