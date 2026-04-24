<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';

const props = defineProps({
    roles: Array,
    permissions: Array,
    groups: Array,
});

const buildInitialPermissions = () => Object.fromEntries(
    props.roles.map((role) => [role.id, [...role.permission_ids]])
);

const form = useForm({
    permissions: buildInitialPermissions(),
});


const selectedRoleId = ref(props.roles[0]?.id ?? null);
const showToast = ref(false);
const toastMessage = ref('');

const triggerToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

watch(
    () => props.roles,
    (roles) => {
        if (!roles.length) {
            selectedRoleId.value = null;
            return;
        }

        const roleStillExists = roles.some((role) => role.id === selectedRoleId.value);
        if (!roleStillExists) {
            selectedRoleId.value = roles[0].id;
        }
    },
    { immediate: true },
);

const ensureRoleBucket = (roleId = selectedRoleId.value) => {
    if (roleId === null || roleId === undefined) {
        return [];
    }

    if (!Array.isArray(form.permissions[roleId])) {
        form.permissions[roleId] = [];
    }

    return form.permissions[roleId];
};



const getActionType = (permissionName) => {
    const name = permissionName.toLowerCase();
    if (name.includes('.create') || name.includes('.store')) return 'create';
    if (name.includes('.update') || name.includes('.edit') || name.includes('.patch')) return 'update';
    if (name.includes('.delete') || name.includes('.destroy')) return 'delete';
    if (name.includes('.view') || name.includes('.index') || name.includes('.show') || name.includes('.read')) return 'view';
    return 'other'; // Becomes 'Full Control' or 'Other'
};

const groupedPermissions = computed(() => {
    const groupMap = new Map(
        props.groups.map((group) => [group.key, { ...group, items: [] }]),
    );

    props.permissions.forEach((permission) => {
        if (!groupMap.has(permission.group_key)) {
            groupMap.set(permission.group_key, {
                key: permission.group_key,
                label: permission.group_label,
                order: permission.group_order,
                items: [],
            });
        }

        groupMap.get(permission.group_key).items.push({
            ...permission,
            action_type: getActionType(permission.name)
        });
    });

    return Array.from(groupMap.values())
        .filter((group) => group.items.length > 0)
        .sort((left, right) => left.order - right.order);
});

const isPermissionEnabled = (permissionId, roleId = selectedRoleId.value) => {
    return ensureRoleBucket(roleId).includes(permissionId);
};

const setPermissionEnabled = (permissionId, enabled, roleId = selectedRoleId.value) => {
    if (roleId === null || roleId === undefined) {
        return;
    }

    const bucket = ensureRoleBucket(roleId);
    const hasPermission = bucket.includes(permissionId);

    if (enabled && !hasPermission) {
        form.permissions[roleId] = [...bucket, permissionId];
    }

    if (!enabled && hasPermission) {
        form.permissions[roleId] = bucket.filter((id) => id !== permissionId);
    }
};

const isGroupEnabled = (group) => {
    if (!group.items.length) {
        return false;
    }

    return group.items.every((permission) => isPermissionEnabled(permission.id));
};

const setGroupEnabled = (group, enabled) => {
    group.items.forEach((permission) => {
        setPermissionEnabled(permission.id, enabled);
    });
};

const submit = () => {
    form.post(route('master.permissions.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.defaults();
        },
    });
};

const cancelChanges = () => {
    form.reset('permissions');
    form.clearErrors();
    triggerToast('Perubahan telah dibatalkan');
};
</script>

<template>
    <Head title="Hak Akses Management" />

    <AppLayout>
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-800">Hak Akses Management</h1>
            </div>

            <!-- Role Tabs -->
            <div class="mb-6 border-b border-slate-200 overflow-x-auto no-scrollbar">
                <div class="flex gap-8 min-w-max px-2">
                    <button
                        v-for="role in roles"
                        :key="role.id"
                        @click="selectedRoleId = role.id"
                        class="pb-4 text-[11px] font-black tracking-widest uppercase transition-all relative"
                        :class="selectedRoleId === role.id ? 'text-blue-600' : 'text-slate-500 hover:text-slate-700'"
                    >
                        {{ role.label }}
                        <div
                            v-if="selectedRoleId === role.id"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-500 rounded-full"
                        ></div>
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1000px]">
                        <thead>
                            <tr class="bg-white border-b border-slate-100">
                                <th class="px-6 py-4 text-[11px] font-black text-slate-700 uppercase tracking-wider w-[80px] text-center">Status</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-700 uppercase tracking-wider">Menu Access</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-700 uppercase tracking-wider w-[100px] text-center">Create</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-700 uppercase tracking-wider w-[100px] text-center">Update</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-700 uppercase tracking-wider w-[100px] text-center">Delete</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-700 uppercase tracking-wider w-[100px] text-center">View</th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-700 uppercase tracking-wider w-[120px] text-center">Full Control</th>
                            </tr>
                        </thead>

                        <tbody class="text-sm">
                            <template v-for="group in groupedPermissions" :key="group.key">
                                <!-- Group Header -->
                                <tr class="bg-slate-50/50">
                                    <td class="px-6 py-3 text-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                class="sr-only peer"
                                                :checked="isGroupEnabled(group)"
                                                @change="setGroupEnabled(group, $event.target.checked)"
                                            >
                                            <div class="w-10 h-5 bg-slate-300 peer-checked:bg-amber-400 rounded-full transition-all relative">
                                                <div class="absolute top-1 left-1 w-3 h-3 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                            </div>
                                        </label>
                                    </td>
                                    <td colspan="6" class="px-6 py-3">
                                        <span class="font-black text-slate-800 uppercase tracking-wide text-xs">{{ group.label }}</span>
                                    </td>
                                </tr>

                                <!-- Permission Rows -->
                                <tr
                                    v-for="permission in group.items"
                                    :key="permission.id"
                                    class="border-b border-slate-50 hover:bg-slate-50/30 transition-colors"
                                >
                                    <td class="px-6 py-4 text-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                class="sr-only peer"
                                                :checked="isPermissionEnabled(permission.id)"
                                                @change="setPermissionEnabled(permission.id, $event.target.checked)"
                                            >
                                            <div class="w-10 h-5 bg-slate-300 peer-checked:bg-amber-400 rounded-full transition-all relative">
                                                <div class="absolute top-1 left-1 w-3 h-3 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                                            </div>
                                        </label>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-700 uppercase text-[12px] tracking-tight">{{ permission.label }}</span>
                                            <span class="text-[10px] text-slate-400 mt-0.5">{{ permission.description }}</span>
                                        </div>
                                    </td>
                                    
                                    <!-- Action Columns -->
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            type="button"
                                            @click="permission.action_type === 'create' || permission.action_type === 'other' ? setPermissionEnabled(permission.id, !isPermissionEnabled(permission.id)) : null"
                                            :disabled="permission.action_type !== 'create' && permission.action_type !== 'other'"
                                            class="inline-flex items-center justify-center w-5 h-5 rounded border-2 transition-all"
                                            :class="[
                                                permission.action_type !== 'create' && permission.action_type !== 'other' ? 'opacity-10 cursor-not-allowed border-slate-200' : 'cursor-pointer',
                                                isPermissionEnabled(permission.id) && permission.action_type === 'create' ? 'bg-blue-600 border-blue-600' : 'bg-white border-slate-300'
                                            ]"
                                        >
                                            <svg v-if="isPermissionEnabled(permission.id) && permission.action_type === 'create'" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            type="button"
                                            @click="permission.action_type === 'update' || permission.action_type === 'other' ? setPermissionEnabled(permission.id, !isPermissionEnabled(permission.id)) : null"
                                            :disabled="permission.action_type !== 'update' && permission.action_type !== 'other'"
                                            class="inline-flex items-center justify-center w-5 h-5 rounded border-2 transition-all"
                                            :class="[
                                                permission.action_type !== 'update' && permission.action_type !== 'other' ? 'opacity-10 cursor-not-allowed border-slate-200' : 'cursor-pointer',
                                                isPermissionEnabled(permission.id) && permission.action_type === 'update' ? 'bg-blue-600 border-blue-600' : 'bg-white border-slate-300'
                                            ]"
                                        >
                                            <svg v-if="isPermissionEnabled(permission.id) && permission.action_type === 'update'" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            type="button"
                                            @click="permission.action_type === 'delete' || permission.action_type === 'other' ? setPermissionEnabled(permission.id, !isPermissionEnabled(permission.id)) : null"
                                            :disabled="permission.action_type !== 'delete' && permission.action_type !== 'other'"
                                            class="inline-flex items-center justify-center w-5 h-5 rounded border-2 transition-all"
                                            :class="[
                                                permission.action_type !== 'delete' && permission.action_type !== 'other' ? 'opacity-10 cursor-not-allowed border-slate-200' : 'cursor-pointer',
                                                isPermissionEnabled(permission.id) && permission.action_type === 'delete' ? 'bg-blue-600 border-blue-600' : 'bg-white border-slate-300'
                                            ]"
                                        >
                                            <svg v-if="isPermissionEnabled(permission.id) && permission.action_type === 'delete'" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            type="button"
                                            @click="permission.action_type === 'view' || permission.action_type === 'other' ? setPermissionEnabled(permission.id, !isPermissionEnabled(permission.id)) : null"
                                            :disabled="permission.action_type !== 'view' && permission.action_type !== 'other'"
                                            class="inline-flex items-center justify-center w-5 h-5 rounded border-2 transition-all"
                                            :class="[
                                                permission.action_type !== 'view' && permission.action_type !== 'other' ? 'opacity-10 cursor-not-allowed border-slate-200' : 'cursor-pointer',
                                                isPermissionEnabled(permission.id) && (permission.action_type === 'view' || permission.action_type === 'other') ? 'bg-blue-600 border-blue-600' : 'bg-white border-slate-300'
                                            ]"
                                        >
                                            <svg v-if="isPermissionEnabled(permission.id) && (permission.action_type === 'view' || permission.action_type === 'other')" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            type="button"
                                            @click="setPermissionEnabled(permission.id, !isPermissionEnabled(permission.id))"
                                            class="inline-flex items-center justify-center w-5 h-5 rounded border-2 transition-all cursor-pointer"
                                            :class="isPermissionEnabled(permission.id) ? 'bg-blue-600 border-blue-600' : 'bg-white border-slate-300'"
                                        >
                                            <svg v-if="isPermissionEnabled(permission.id)" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-slate-100 bg-slate-50/60 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="cancelChanges"
                        :disabled="form.processing || !form.isDirty"
                        class="px-8 py-2.5 bg-white hover:bg-slate-100 disabled:bg-slate-100 disabled:text-slate-400 text-slate-700 text-sm font-bold rounded-xl border border-slate-200 transition-all active:scale-95"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing || !form.isDirty"
                        class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-blue-500/20 active:scale-95"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showToast" class="fixed bottom-8 right-8 z-[100]">
                <div class="bg-slate-900 text-white px-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-700/50 backdrop-blur-xl">
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-bold tracking-wide">{{ toastMessage }}</span>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
