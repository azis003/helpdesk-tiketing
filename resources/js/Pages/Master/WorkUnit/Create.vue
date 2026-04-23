<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';

const form = useForm({
    name: '',
    code: '',
});

const submit = () => {
    form.post(route('master.work-units.store'));
};
</script>

<template>
    <Head title="Tambah Unit Kerja" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Unit Kerja</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="code" class="block font-medium text-sm text-gray-700">Kode Unit Kerja</label>
                                <input id="code" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.code" required autofocus />
                                <div v-if="form.errors.code" class="text-sm text-red-600 mt-1">{{ form.errors.code }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="name" class="block font-medium text-sm text-gray-700">Nama Unit Kerja</label>
                                <input id="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" v-model="form.name" required />
                                <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div class="flex items-center justify-end mt-4 gap-4">
                                <Link :href="route('master.work-units.index')" class="text-gray-600 hover:text-gray-900 underline">
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
