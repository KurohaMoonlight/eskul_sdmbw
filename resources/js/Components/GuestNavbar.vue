<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

// Efek navbar transparan ke solid saat discroll
const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));
</script>

<template>
    <nav 
        class="fixed top-0 z-50 w-full transition-all duration-300 border-b border-transparent"
        :class="isScrolled ? 'bg-[#213448]/95 backdrop-blur-md shadow-md border-[#547792]/30 py-3' : 'bg-transparent py-5'"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                
                <!-- Logo / Brand -->
                <div class="flex items-center gap-3">
                    <!-- Placeholder Logo (Bisa diganti img) -->
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#EAE0CF] text-[#213448] shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold leading-none text-[#EAE0CF] tracking-wide">SD Muhammadiyah</span>
                        <span class="text-xs font-medium text-[#94B4C1] tracking-widest">BIRRUL WALIDAIN</span>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#beranda" class="text-sm font-medium text-[#EAE0CF] hover:text-white transition">Beranda</a>
                    <a href="#tentang" class="text-sm font-medium text-[#94B4C1] hover:text-[#EAE0CF] transition">Tentang</a>
                    <a href="#prestasi" class="text-sm font-medium text-[#94B4C1] hover:text-[#EAE0CF] transition">Prestasi</a>
                    <a href="#kontak" class="text-sm font-medium text-[#94B4C1] hover:text-[#EAE0CF] transition">Kontak</a>
                </div>

                <!-- Login Button -->
                <div class="hidden md:flex items-center gap-3">
                    <Link 
                        href="/login" 
                        class="group relative overflow-hidden rounded-full bg-[#EAE0CF] px-6 py-2 text-sm font-bold text-[#213448] shadow-lg transition-all hover:bg-white hover:scale-105 active:scale-95"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            Login Sistem
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </span>
                    </Link>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="md:hidden rounded-lg p-2 text-[#EAE0CF] hover:bg-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div v-if="isMobileMenuOpen" class="md:hidden bg-[#1a2a3a] border-t border-[#547792]/30 px-4 py-4 space-y-4 shadow-2xl">
            <a href="#beranda" class="block text-[#EAE0CF] font-medium">Beranda</a>
            <a href="#prestasi" class="block text-[#94B4C1]">Prestasi</a>
            <Link href="/login" class="block w-full text-center rounded-lg bg-[#EAE0CF] py-3 text-sm font-bold text-[#213448]">
                Login Sistem
            </Link>
        </div>
    </nav>
</template>