<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Sidebar from './Sidebar.vue';
import Navbar from './Navbar.vue';

const isSidebarCollapsed = ref(false);
const page = usePage();

const showSuccess = ref(false);
const showError = ref(false);
let successTimer = null;
let errorTimer = null;

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

// Handle flash messages on every navigation finish
router.on('finish', () => {
    const success = page.props.flash.success;
    const error = page.props.flash.error;

    if (success) {
        showSuccess.value = false; // Reset to trigger transition
        setTimeout(() => {
            showSuccess.value = true;
            if (successTimer) clearTimeout(successTimer);
            successTimer = setTimeout(() => {
                showSuccess.value = false;
            }, 3000);
        }, 50);
    }

    if (error) {
        showError.value = false;
        setTimeout(() => {
            showError.value = true;
            if (errorTimer) clearTimeout(errorTimer);
            errorTimer = setTimeout(() => {
                showError.value = false;
            }, 5000);
        }, 50);
    }
});

// Initial check
onMounted(() => {
    if (page.props.flash.success) {
        showSuccess.value = true;
        successTimer = setTimeout(() => showSuccess.value = false, 3000);
    }
    if (page.props.flash.error) {
        showError.value = true;
        errorTimer = setTimeout(() => showError.value = false, 5000);
    }
});
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
        <transition name="toast">
            <div v-if="showSuccess && $page.props.flash.success" class="fixed bottom-6 right-6 z-50">
                <div class="bg-emerald-500 text-white px-6 py-3 rounded-xl shadow-xl flex items-center group relative">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="font-semibold pr-6">{{ $page.props.flash.success }}</span>
                    <button @click="showSuccess = false" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-white/20 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </transition>
        
        <transition name="toast">
            <div v-if="showError && $page.props.flash.error" class="fixed bottom-6 right-6 z-50">
                <div class="bg-red-500 text-white px-6 py-3 rounded-xl shadow-xl flex items-center group relative">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="font-semibold pr-6">{{ $page.props.flash.error }}</span>
                    <button @click="showError = false" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-white/20 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.toast-enter-active {
    animation: toast-in 0.5s ease-out;
}
.toast-leave-active {
    animation: toast-out 0.5s ease-in forwards;
}

@keyframes toast-in {
    0% { transform: translateY(100px); opacity: 0; }
    60% { transform: translateY(-10px); }
    100% { transform: translateY(0); opacity: 1; }
}

@keyframes toast-out {
    0% { transform: translateY(0); opacity: 1; }
    100% { transform: translateY(100px); opacity: 0; }
}
</style>
