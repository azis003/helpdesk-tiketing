<script setup>
import { ref } from 'vue';
import Sidebar from './Sidebar.vue';
import Navbar from './Navbar.vue';

const isSidebarCollapsed = ref(false);

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 font-sans">
        <!-- Sidebar -->
        <Sidebar :collapsed="isSidebarCollapsed" />

        <!-- Main Content area -->
        <div 
            class="transition-all duration-300"
            :class="isSidebarCollapsed ? 'pl-[72px]' : 'pl-64'"
        >
            <Navbar @toggle-sidebar="toggleSidebar" />

            <main class="p-6 md:p-8 max-w-7xl mx-auto">
                <slot />
            </main>
        </div>

        <!-- Notification container (Global) -->
        <div v-if="$page.props.flash.success" class="fixed bottom-6 right-6 z-50 animate-bounce-in">
            <div class="bg-emerald-500 text-white px-6 py-3 rounded-xl shadow-xl flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-semibold">{{ $page.props.flash.success }}</span>
            </div>
        </div>
        
        <div v-if="$page.props.flash.error" class="fixed bottom-6 right-6 z-50 animate-bounce-in">
            <div class="bg-red-500 text-white px-6 py-3 rounded-xl shadow-xl flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="font-semibold">{{ $page.props.flash.error }}</span>
            </div>
        </div>
    </div>
</template>

<style>
@keyframes bounce-in {
    0% { transform: translateY(100px); opacity: 0; }
    60% { transform: translateY(-10px); }
    100% { transform: translateY(0); opacity: 1; }
}
.animate-bounce-in {
    animation: bounce-in 0.5s ease-out forwards;
}
</style>
