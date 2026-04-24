<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Modal from '@/Components/UI/Modal.vue';
import debounce from 'lodash/debounce';
import Swal from 'sweetalert2';

const props = defineProps({
    categories: Object,
    filters: Object,
});

// State
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedCategory = ref(null);
const search = ref(props.filters.search || '');

// Forms
const createForm = useForm({
    name: '',
    description: '',
});

const editForm = useForm({
    name: '',
    description: '',
});

const toggleForm = useForm({});

// Actions
const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
};

const submitCreate = () => {
    createForm.post(route('master.categories.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (category) => {
    selectedCategory.value = category;
    editForm.name = category.name;
    editForm.description = category.description;
    editForm.clearErrors();
    showEditModal.value = true;
};

const submitUpdate = () => {
    editForm.put(route('master.categories.update', selectedCategory.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
    });
};

const toggleActive = (category) => {
    Swal.fire({
        title: 'Ubah Status?',
        text: `Apakah Anda yakin ingin ${category.is_active ? 'menonaktifkan' : 'mengaktifkan'} kategori ini?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            toggleForm.patch(route('master.categories.toggle-active', category.id), {
                preserveScroll: true,
                onSuccess: () => {
                    // Optional: sweetalert success can also be triggered here if AppLayout flash fails
                }
            });
        }
    });
};

// Search with debounce
watch(search, debounce((value) => {
    router.get(route('master.categories.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

</script>

<template>
    <Head title="Master Kategori" />

    <AppLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-slate-900 tracking-tight">Master Kategori</h2>
                <p class="text-slate-500 mt-1">Kelola daftar kategori tiket.</p>
            </div>
            
            <button 
                type="button"
                @click="openCreateModal"
                class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 group"
            >
                <svg class="w-5 h-5 mr-2 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v12m6-6H6" />
                </svg>
                Tambah Kategori
            </button>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <div class="relative max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Cari nama atau deskripsi kategori..." 
                        class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider">
                            <th class="px-8 py-5 font-semibold">Kategori</th>
                            <th class="px-8 py-5 font-semibold text-center">Jml Tiket</th>
                            <th class="px-8 py-5 font-semibold text-center">Status</th>
                            <th class="px-8 py-5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="category in categories.data" :key="category.id" class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="font-bold text-slate-800">{{ category.name }}</div>
                                <div class="text-sm text-slate-400 mt-0.5 line-clamp-1">{{ category.description || '-' }}</div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <span class="inline-flex items-center justify-center px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">
                                    {{ category.tickets_count }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex justify-center">
                                    <button 
                                        type="button"
                                        @click="toggleActive(category)"
                                        :disabled="toggleForm.processing"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                                        :class="category.is_active ? 'bg-blue-600' : 'bg-slate-200'"
                                    >
                                        <span
                                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                            :class="category.is_active ? 'translate-x-6' : 'translate-x-1'"
                                        />
                                    </button>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex justify-end items-center gap-3">
                                    <button 
                                        type="button"
                                        @click="openEditModal(category)"
                                        class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all"
                                        title="Edit"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="categories.data.length === 0">
                            <td colspan="4" class="px-8 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="p-4 bg-slate-50 rounded-full mb-3 text-slate-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                    </div>
                                    <span class="text-slate-400 font-medium">Tidak ada data kategori ditemukan.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex justify-center">
                <Pagination :links="categories.links" />
            </div>
        </div>

        <!-- Modal Tambah -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" maxWidth="xl">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-extrabold text-slate-900">Tambah Kategori</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Kategori <span class="text-red-500">*</span>
                        </label>
                        <input 
                            id="name" 
                            type="text" 
                            v-model="createForm.name"
                            class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600 font-medium"
                            placeholder="Contoh: Hardware, Software, dll."
                            required
                        />
                        <div v-if="createForm.errors.name" class="text-sm text-red-500 mt-2 font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ createForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-bold text-slate-700 mb-2">Deskripsi (Opsional)</label>
                        <textarea 
                            id="description" 
                            v-model="createForm.description"
                            rows="3"
                            class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600 font-medium"
                            placeholder="Berikan penjelasan singkat tentang kategori ini..."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button 
                            type="button" 
                            @click="showCreateModal = false"
                            :disabled="createForm.processing"
                            class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-2xl transition-all disabled:opacity-50"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="createForm.processing"
                            class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 disabled:opacity-50 flex items-center"
                        >
                            <svg v-if="createForm.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-if="createForm.processing">Menyimpan...</span>
                            <span v-else>Simpan Kategori</span>
                        </button>
                    </div>
                </form>

                <!-- Loading Overlay -->
                <div v-if="createForm.processing" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center z-[100]">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
                        <p class="mt-4 text-blue-600 font-bold text-sm tracking-wide">Memproses Data...</p>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Modal Edit -->
        <Modal :show="showEditModal" @close="!editForm.processing && (showEditModal = false)" maxWidth="xl">
            <div class="p-8 relative">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-extrabold text-slate-900">Edit Kategori</h3>
                    <button @click="showEditModal = false" :disabled="editForm.processing" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl transition-all disabled:opacity-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitUpdate" class="space-y-6">
                    <div>
                        <label for="edit_name" class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Kategori <span class="text-red-500">*</span>
                        </label>
                        <input 
                            id="edit_name" 
                            type="text" 
                            v-model="editForm.name"
                            class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600 font-medium"
                            required
                        />
                        <div v-if="editForm.errors.name" class="text-sm text-red-500 mt-2 font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ editForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label for="edit_description" class="block text-sm font-bold text-slate-700 mb-2">Deskripsi (Opsional)</label>
                        <textarea 
                            id="edit_description" 
                            v-model="editForm.description"
                            rows="3"
                            class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600 font-medium"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button 
                            type="button" 
                            @click="showEditModal = false"
                            :disabled="editForm.processing"
                            class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-2xl transition-all disabled:opacity-50"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="editForm.processing"
                            class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 disabled:opacity-50 flex items-center"
                        >
                            <svg v-if="editForm.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-if="editForm.processing">Menyimpan...</span>
                            <span v-else>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>

                <!-- Loading Overlay -->
                <div v-if="editForm.processing" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center z-[100]">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
                        <p class="mt-4 text-blue-600 font-bold text-sm tracking-wide">Memperbarui Data...</p>
                    </div>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
