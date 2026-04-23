<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed } from 'vue';

const props = defineProps({
    collapsed: Boolean
});

const page = usePage();
const permissions = computed(() => page.props.auth.user.permissions);

const can = (permission) => {
    if (Array.isArray(permission)) {
        return permission.some(p => permissions.value.includes(p));
    }
    return permissions.value.includes(permission);
};

const menuGroups = [
    {
        title: 'Utama',
        items: [
            { name: 'Dashboard', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', permission: ['dashboard.personal', 'dashboard.operational'] },
            { name: 'Tiket Saya', route: 'dashboard', icon: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5z', permission: 'ticket.view' },
        ]
    },
    {
        title: 'Master Data',
        items: [
            { name: 'Kategori Tiket', route: 'master.categories.index', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', permission: 'master.category' },
            { name: 'Level Prioritas', route: 'master.priorities.index', icon: 'M13 10V3L4 14h7v7l9-11h-7z', permission: 'master.priority' },
            { name: 'Unit Kerja', route: 'master.work-units.index', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', permission: 'master.work-unit' },
        ]
    },
    {
        title: 'Pengaturan',
        items: [
            { name: 'Manajemen Pengguna', route: 'master.users.index', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', permission: 'master.user' },
            { name: 'Hak Akses & Peran', route: 'master.permissions.index', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-7.618 3.04c-.753 0-1.487.06-2.201.174A11.03 11.03 0 002 10c0 5.008 3.358 9.228 8 10.438 4.642-1.21 8-5.43 8-10.438 0-1.32-.234-2.585-.662-3.758a12.015 12.015 0 01-1.72-2.7z', permission: 'master.permission' },
        ]
    },
    {
        title: 'Laporan',
        items: [
            { name: 'Laporan Tiket', route: 'dashboard', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z', permission: ['report.export', 'report.personal'] },
        ]
    }
];

const filteredGroups = computed(() => {
    return menuGroups.map(group => {
        return {
            ...group,
            items: group.items.filter(item => can(item.permission))
        };
    }).filter(group => group.items.length > 0);
});
</script>

<template>
    <aside 
        class="fixed left-0 top-0 h-screen bg-white border-r border-slate-200 transition-all duration-300 z-50 flex flex-col"
        :class="collapsed ? 'w-[72px]' : 'w-64'"
    >
        <!-- Logo Area -->
        <div class="h-16 flex items-center px-5 shrink-0 border-b border-slate-100 bg-white/95 backdrop-blur-sm z-10">
            <div class="w-8 h-8 bg-blue-600 rounded-xl flex items-center justify-center shrink-0 shadow-sm shadow-blue-600/20">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div 
                v-if="!collapsed" 
                class="ml-3 flex flex-col justify-center overflow-hidden transition-opacity duration-300"
                :class="collapsed ? 'opacity-0' : 'opacity-100'"
            >
                <span class="font-bold text-slate-900 tracking-tight text-[15px] leading-tight">Helpdesk</span>
                <span class="text-[10px] text-blue-600 font-bold tracking-wider uppercase">BP2KOMDIGI</span>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-6 space-y-6 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:'none'] [scrollbar-width:'none']">
            <div v-for="group in filteredGroups" :key="group.title">
                <!-- Group Title -->
                <p 
                    v-if="!collapsed" 
                    class="px-3 mb-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase transition-opacity duration-300"
                >
                    {{ group.title }}
                </p>
                <div v-else class="h-4"></div> <!-- Spacer for collapsed state -->
                
                <!-- Group Items -->
                <div class="space-y-1">
                    <Link 
                        v-for="item in group.items" 
                        :key="item.name"
                        :href="route(item.route)"
                        class="flex items-center px-3 py-2.5 rounded-xl transition-all group relative"
                        :class="route().current(item.route) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
                        :title="collapsed ? item.name : ''"
                    >
                        <!-- Active indicator line -->
                        <div v-if="route().current(item.route)" class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-blue-600 rounded-r-full"></div>
                        
                        <svg 
                            class="w-[20px] h-[20px] shrink-0 transition-colors"
                            :class="route().current(item.route) ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600'"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                        </svg>
                        
                        <span 
                            v-if="!collapsed" 
                            class="ml-3.5 text-[13px] whitespace-nowrap transition-opacity duration-300"
                            :class="collapsed ? 'opacity-0' : 'opacity-100'"
                        >
                            {{ item.name }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>
        
        <!-- Bottom Area for Collapsed State Consistency -->
        <div class="h-4 shrink-0"></div>
    </aside>
</template>
