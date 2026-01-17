<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <nav class="sticky top-0 z-50 w-full bg-[#213448] text-[#EAE0CF] shadow-lg">
        <div class="mx-auto flex h-16 max-w-full items-center justify-between px-4 sm:px-6 lg:px-8">
            
            <!-- BAGIAN KIRI: Identitas (Tanpa Logo) -->
            <div class="flex items-center gap-4">
                <!-- Teks: Selalu Muncul (Flex) baik Mobile maupun Desktop -->
                <div class="flex flex-col items-start text-left">
                    <span class="text-sm font-bold leading-tight tracking-wide md:text-base">SD Muhammadiyah Birrul Walidain</span>
                    <span class="text-[10px] font-medium text-[#94B4C1] uppercase tracking-wider">E-Eskul System</span>
                </div>
            </div>

            <!-- BAGIAN TENGAH/KANAN (DESKTOP) -->
            <div class="hidden items-center md:flex">
                <!-- Slot Navigasi Desktop -->
                <div class="flex items-center gap-4 pr-6">
                     <slot />
                </div>

                <!-- Separator Desktop -->
                <div class="h-8 w-px bg-gradient-to-b from-transparent via-[#547792] to-transparent opacity-50"></div>

                <!-- Logout Desktop -->
                <div class="pl-6">
                    <button 
                        @click="logout"
                        class="group relative flex items-center gap-2 overflow-hidden rounded-full bg-[#EAE0CF]/10 px-5 py-2 text-sm font-semibold transition-all duration-300 hover:bg-[#EAE0CF] hover:text-[#213448] hover:shadow-[0_0_15px_rgba(234,224,207,0.3)]"
                    >
                        <span>Logout</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- BAGIAN KANAN (MOBILE HAMBURGER) -->
            <div class="flex items-center md:hidden">
                <button 
                    @click="toggleMobileMenu" 
                    class="rounded-md p-2 text-[#94B4C1] hover:bg-[#EAE0CF]/10 hover:text-[#EAE0CF] focus:outline-none"
                >
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- MOBILE SIDEBAR / OFF-CANVAS MENU -->
        <div v-show="isMobileMenuOpen" class="fixed inset-0 z-[60] flex md:hidden">
            
            <!-- Overlay Gelap (Klik untuk tutup) -->
            <div 
                class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" 
                @click="toggleMobileMenu"
            ></div>

            <!-- Sidebar Panel -->
            <div class="relative flex w-64 max-w-xs flex-col bg-[#213448] shadow-xl transition-transform duration-300 ease-in-out border-r border-[#547792]/30">
                
                <!-- Header Sidebar -->
                <div class="flex h-16 items-center justify-between px-4 border-b border-[#547792]/30">
                    <span class="text-lg font-bold text-[#EAE0CF]">Menu</span>
                    <button @click="toggleMobileMenu" class="rounded-md p-1 text-[#94B4C1] hover:text-[#EAE0CF]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Konten Menu (Scrollable) -->
                <div class="flex-1 overflow-y-auto px-4 py-6">
                    <!-- Slot Navigasi Mobile (Vertical Stack) -->
                    <div class="flex flex-col space-y-4">
                        <slot />
                    </div>
                </div>

                <!-- Footer Sidebar (Logout di Paling Bawah) -->
                <div class="border-t border-[#547792]/30 p-4">
                    <button 
                        @click="logout"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#EAE0CF] px-4 py-3 text-sm font-bold text-[#213448] shadow-md transition-transform active:scale-95"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </div>

            </div>
        </div>
    </nav>
</template>