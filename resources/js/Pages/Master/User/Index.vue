<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { onUnmounted, ref, watch } from 'vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Modal from '@/Components/UI/Modal.vue';
import debounce from 'lodash/debounce';
import Swal from 'sweetalert2';

const props = defineProps({
    users: Object,
    roles: Array,
    workUnits: Array,
    filters: Object,
});

// State
const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedUser = ref(null);
const search = ref(props.filters.search || '');

// Forms
const createForm = useForm({
    username: '',
    name: '',
    email: '',
    password: '',
    roles: [],
    work_unit_id: '',
});

const editForm = useForm({
    name: '',
    email: '',
    password: '',
    roles: [],
    work_unit_id: '',
});

const toggleForm = useForm({});
const normalizeWorkUnitId = (value) => value === '' ? null : value;
const getRolesError = (form) => {
    if (form.errors.roles) {
        return form.errors.roles;
    }

    const nestedRoleError = Object.entries(form.errors).find(([field]) => field.startsWith('roles.'));
    return nestedRoleError ? nestedRoleError[1] : null;
};

// Actions
const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
};

const submitCreate = () => {
    createForm.transform((data) => ({
        ...data,
        work_unit_id: normalizeWorkUnitId(data.work_unit_id),
    })).post(route('master.users.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (user) => {
    selectedUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email || '';
    editForm.password = '';
    editForm.roles = Array.isArray(user.roles) ? user.roles.map((role) => role.name) : [];
    editForm.work_unit_id = user.work_unit_id || '';
    editForm.clearErrors();
    showEditModal.value = true;
};

const submitUpdate = () => {
    editForm.transform((data) => ({
        ...data,
        work_unit_id: normalizeWorkUnitId(data.work_unit_id),
    })).put(route('master.users.update', selectedUser.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
    });
};

const toggleActive = (user) => {
    Swal.fire({
        title: 'Ubah Status?',
        text: `Apakah Anda yakin ingin ${user.is_active ? 'menonaktifkan' : 'mengaktifkan'} pengguna ini?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            toggleForm.patch(route('master.users.toggle-active', user.id), {
                preserveScroll: true,
                onError: (errors) => {
                    if (errors.is_active) {
                        Swal.fire('Gagal!', errors.is_active, 'error');
                    }
                }
            });
        }
    });
};

// Search with debounce
const debouncedSearch = debounce((value) => {
    router.get(route('master.users.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(search, debouncedSearch);

onUnmounted(() => {
    debouncedSearch.cancel();
});

const getInitial = (name) => {
    return name ? name.charAt(0).toUpperCase() : '?';
};
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <AppLayout>
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-slate-900 tracking-tight">Manajemen Pengguna</h2>
                <p class="text-slate-500 mt-1">Kelola akun pegawai dan akses sistem.</p>
            </div>
            
            <button 
                type="button"
                @click="openCreateModal"
                class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 group"
            >
                <svg class="w-5 h-5 mr-2 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v12m6-6H6" />
                </svg>
                Tambah Pengguna
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
                        placeholder="Cari nama atau username..." 
                        class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-slate-600"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider">
                            <th class="px-8 py-5 font-semibold">Pengguna</th>
                            <th class="px-8 py-5 font-semibold">Kontak</th>
                            <th class="px-8 py-5 font-semibold">Role & Unit</th>
                            <th class="px-8 py-5 font-semibold text-center">Status</th>
                            <th class="px-8 py-5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg mr-4 border border-blue-100 shadow-sm">
                                        {{ getInitial(user.name) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ user.name }}</div>
                                        <div class="text-xs text-slate-400 font-medium">@{{ user.username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-sm">
                                <div class="text-slate-600 font-medium">{{ user.email || '-' }}</div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex flex-col gap-1">
                                    <div class="flex gap-1">
                                        <span v-for="role in user.roles" :key="role.id" class="inline-flex px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold uppercase tracking-wider border border-blue-100">
                                            {{ role.name }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-400 italic">{{ user.work_unit?.name || 'Tanpa Unit' }}</div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex justify-center">
                                    <button 
                                        type="button"
                                        @click="toggleActive(user)"
                                        :disabled="toggleForm.processing"
                                        :aria-label="`${user.is_active ? 'Nonaktifkan' : 'Aktifkan'} pengguna ${user.name}`"
                                        :aria-checked="user.is_active ? 'true' : 'false'"
                                        role="switch"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                                        :class="user.is_active ? 'bg-blue-600' : 'bg-slate-200'"
                                    >
                                        <span
                                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                            :class="user.is_active ? 'translate-x-6' : 'translate-x-1'"
                                        />
                                    </button>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex justify-end items-center gap-2">
                                    <button 
                                        type="button"
                                        @click="openEditModal(user)"
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
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="px-8 py-12 text-center text-slate-400 italic">
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex justify-center">
                <Pagination :links="users.links" />
            </div>
        </div>

        <!-- Modal Tambah -->
        <Modal :show="showCreateModal" @close="!createForm.processing && (showCreateModal = false)" maxWidth="2xl">
            <div class="p-8 relative">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-extrabold text-slate-900">Tambah Pengguna</h3>
                    <button @click="showCreateModal = false" :disabled="createForm.processing" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl transition-all disabled:opacity-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input v-model="createForm.username" type="text" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" required />
                            <div v-if="createForm.errors.username" class="text-xs text-red-500 mt-2 font-medium">{{ createForm.errors.username }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input v-model="createForm.name" type="text" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" required />
                            <div v-if="createForm.errors.name" class="text-xs text-red-500 mt-2 font-medium">{{ createForm.errors.name }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                            <input v-model="createForm.email" type="email" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" />
                            <div v-if="createForm.errors.email" class="text-xs text-red-500 mt-2 font-medium">{{ createForm.errors.email }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input v-model="createForm.password" type="password" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" required />
                            <div v-if="createForm.errors.password" class="text-xs text-red-500 mt-2 font-medium">{{ createForm.errors.password }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Role <span class="text-red-500">*</span>
                            </label>
                            <select v-model="createForm.roles" multiple class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none min-h-28" required>
                                <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                            </select>
                            <p class="text-xs text-slate-400 mt-2">Pilih satu atau lebih role.</p>
                            <div v-if="getRolesError(createForm)" class="text-xs text-red-500 mt-2 font-medium">{{ getRolesError(createForm) }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Unit Kerja</label>
                            <select v-model="createForm.work_unit_id" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                                <option value="">Tanpa Unit</option>
                                <option v-for="unit in workUnits" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                            </select>
                            <div v-if="createForm.errors.work_unit_id" class="text-xs text-red-500 mt-2 font-medium">{{ createForm.errors.work_unit_id }}</div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showCreateModal = false" :disabled="createForm.processing" class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-2xl transition-all disabled:opacity-50">Batal</button>
                        <button type="submit" :disabled="createForm.processing" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 disabled:opacity-50 flex items-center">
                            <svg v-if="createForm.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-if="createForm.processing">Menyimpan...</span>
                            <span v-else>Simpan User</span>
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
        <Modal :show="showEditModal" @close="!editForm.processing && (showEditModal = false)" maxWidth="2xl">
            <div class="p-8 relative">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-extrabold text-slate-900">Edit Pengguna</h3>
                    <button @click="showEditModal = false" :disabled="editForm.processing" class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-100 rounded-xl transition-all disabled:opacity-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitUpdate" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Username (Tetap)</label>
                            <input :value="selectedUser?.username" type="text" class="block w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-2xl text-slate-400 outline-none" disabled />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input v-model="editForm.name" type="text" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" required />
                            <div v-if="editForm.errors.name" class="text-xs text-red-500 mt-2 font-medium">{{ editForm.errors.name }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                            <input v-model="editForm.email" type="email" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" />
                            <div v-if="editForm.errors.email" class="text-xs text-red-500 mt-2 font-medium">{{ editForm.errors.email }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Password Baru (Kosongkan jika tidak diubah)</label>
                            <input v-model="editForm.password" type="password" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" placeholder="••••••••" />
                            <div v-if="editForm.errors.password" class="text-xs text-red-500 mt-2 font-medium">{{ editForm.errors.password }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Role <span class="text-red-500">*</span>
                            </label>
                            <select v-model="editForm.roles" multiple class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none min-h-28" required>
                                <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                            </select>
                            <p class="text-xs text-slate-400 mt-2">Pilih satu atau lebih role.</p>
                            <div v-if="getRolesError(editForm)" class="text-xs text-red-500 mt-2 font-medium">{{ getRolesError(editForm) }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Unit Kerja</label>
                            <select v-model="editForm.work_unit_id" class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                                <option value="">Tanpa Unit</option>
                                <option v-for="unit in workUnits" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                            </select>
                            <div v-if="editForm.errors.work_unit_id" class="text-xs text-red-500 mt-2 font-medium">{{ editForm.errors.work_unit_id }}</div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showEditModal = false" :disabled="editForm.processing" class="px-6 py-3 text-slate-600 font-bold hover:bg-slate-100 rounded-2xl transition-all disabled:opacity-50">Batal</button>
                        <button type="submit" :disabled="editForm.processing" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95 disabled:opacity-50 flex items-center">
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
