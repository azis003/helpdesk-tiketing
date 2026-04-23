<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Mock data for the UI to showcase the design
const stats = [
    { name: 'Total Tiket', value: '45', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-blue-600', bg: 'bg-blue-50' },
    { name: 'Menunggu Respon', value: '12', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', color: 'text-amber-600', bg: 'bg-amber-50' },
    { name: 'Sedang Dikerjakan', value: '5', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', color: 'text-indigo-600', bg: 'bg-indigo-50' },
    { name: 'Selesai', value: '28', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', color: 'text-emerald-600', bg: 'bg-emerald-50' },
];

const recentTickets = [
    { id: 'TKT-20231001', title: 'Komputer tidak bisa menyala saat pagi', status: 'Baru', date: 'Hari ini, 09:30', priority: 'Tinggi', priorityColor: 'text-red-700 bg-red-50 ring-red-600/10' },
    { id: 'TKT-20231002', title: 'Request pembuatan akses VPN baru', status: 'Diproses', date: 'Kemarin, 14:20', priority: 'Normal', priorityColor: 'text-blue-700 bg-blue-50 ring-blue-600/10' },
    { id: 'TKT-20231003', title: 'Printer di lantai 2 selalu offline', status: 'Selesai', date: '2 hari lalu', priority: 'Rendah', priorityColor: 'text-slate-600 bg-slate-50 ring-slate-500/10' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Overview</h1>
                <p class="text-sm text-slate-500 mt-1">Pantau performa dan status tiket terbaru hari ini.</p>
            </div>
            <div class="flex gap-3">
                <button class="px-4 py-2 bg-white border border-slate-200 text-slate-700 font-semibold text-sm rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export
                </button>
                <button class="px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-xl hover:bg-blue-500 transition-all shadow-sm shadow-blue-600/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Tiket
                </button>
            </div>
        </div>

        <!-- Welcome Banner -->
        <div class="mb-8 relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-sm text-white p-8 sm:p-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="relative z-10">
                <h2 class="text-3xl font-bold tracking-tight mb-3">Selamat datang, {{ user.name }}! 👋</h2>
                <p class="text-blue-100 max-w-xl text-[15px] leading-relaxed">
                    Anda memiliki <strong class="text-white font-semibold bg-white/20 px-2 py-0.5 rounded-md mx-1">12 tiket baru</strong> yang menunggu untuk direspon. Mari selesaikan tugas hari ini dengan semangat!
                </p>
            </div>
            <!-- Decorative Background Pattern -->
            <div class="absolute right-0 top-0 w-64 h-full opacity-10 pointer-events-none">
                <svg viewBox="0 0 100 100" class="w-full h-full transform scale-150 translate-x-10 -translate-y-10" fill="currentColor">
                    <path d="M11.18 50l26.9-26.9-17.68-17.68-26.9 26.9 17.68 17.68zM50 11.18l26.9-26.9-17.68-17.68-26.9 26.9 17.68 17.68zM88.82 50l26.9-26.9-17.68-17.68-26.9 26.9 17.68 17.68z" />
                </svg>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div v-for="stat in stats" :key="stat.name" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group relative overflow-hidden">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[13px] font-semibold text-slate-500 mb-1.5">{{ stat.name }}</p>
                        <h3 class="text-3xl font-bold text-slate-900 tracking-tight">{{ stat.value }}</h3>
                    </div>
                    <div :class="[stat.bg, stat.color, 'w-12 h-12 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110 group-hover:rotate-3']">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tickets Section -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h2 class="font-bold text-slate-900 text-base">Tiket Terbaru</h2>
                <a href="#" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">Lihat Semua &rarr;</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50/50 border-b border-slate-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold tracking-wider">ID Tiket</th>
                            <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Judul</th>
                            <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Prioritas</th>
                            <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Waktu</th>
                            <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="ticket in recentTickets" :key="ticket.id" class="border-b border-slate-50 hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">
                                {{ ticket.id }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-700">{{ ticket.title }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-xs font-semibold border"
                                    :class="{
                                        'bg-blue-50 text-blue-700 border-blue-200': ticket.status === 'Baru',
                                        'bg-amber-50 text-amber-700 border-amber-200': ticket.status === 'Diproses',
                                        'bg-emerald-50 text-emerald-700 border-emerald-200': ticket.status === 'Selesai',
                                    }">
                                    <span class="w-1.5 h-1.5 rounded-full" 
                                        :class="{
                                            'bg-blue-600': ticket.status === 'Baru',
                                            'bg-amber-500': ticket.status === 'Diproses',
                                            'bg-emerald-500': ticket.status === 'Selesai',
                                        }"></span>
                                    {{ ticket.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['inline-flex items-center py-1 px-2 rounded-md text-xs font-semibold ring-1 ring-inset', ticket.priorityColor]">
                                    {{ ticket.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-medium">
                                {{ ticket.date }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-slate-400 hover:text-blue-600 transition-colors p-1.5 rounded-lg group-hover:bg-blue-50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
