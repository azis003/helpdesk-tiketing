<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const logout = () => {
    router.post(route('logout'));
};

defineEmits(['toggle-sidebar']);
</script>

<template>
    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 transition-all duration-300">
        <div class="h-full px-4 flex items-center justify-between">
            <div class="flex items-center">
                <button 
                    @click="$emit('toggle-sidebar')"
                    class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="ml-4 flex items-center md:hidden">
                     <span class="font-bold text-slate-900 tracking-tight">Helpdesk TI</span>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <!-- User Profile info -->
                <div class="flex flex-col items-end mr-2">
                    <span class="text-sm font-semibold text-slate-900 leading-none">{{ user.name }}</span>
                    <span class="text-xs text-slate-500 mt-1 uppercase tracking-wider font-bold">{{ user.role }}</span>
                </div>

                <!-- Dropdown (simplified as links for now) -->
                <div class="flex items-center space-x-2">
                    <Link 
                        :href="route('profile.edit')"
                        class="p-2 rounded-full bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-all"
                        title="Profil Saya"
                    >
                        <svg v-if="!user.avatar" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <img v-else :src="user.avatar" class="w-6 h-6 rounded-full object-cover" />
                    </Link>

                    <button 
                        @click="logout"
                        class="p-2 rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all"
                        title="Logout"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>
