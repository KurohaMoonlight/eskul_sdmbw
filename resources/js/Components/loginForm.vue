<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

// State untuk visibilitas password
const showPassword = ref(false);

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <!-- Main Container: Full Screen, Centered Content, Background Cream #EAE0CF -->
    <div class="flex min-h-screen w-full items-center justify-center overflow-hidden bg-[#EAE0CF] relative px-4 md:px-0">
        
        <!-- Template Logo (Pojok Kiri Atas - Responsive) -->
        <div class="max-h-32 max-w-64 absolute top-2 left-2 md:top-2 md:left-2 z-20">
            <!-- Placeholder Logo -->
            <img src="../../../public/Img/logo.png" class=" hover:scale-105 transition-transform"/>
     
        </div>

        <!-- Background SVG Decoration (Absolute Full Screen) -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <svg class="w-full h-full" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                <!-- Lingkaran Besar Utama (Dark Blue) -->
                <circle cx="720" cy="450" r="400" fill="#213448" opacity="0.05" />
                
                <!-- Lingkaran Medium (Medium Blue) -->
                <circle cx="200" cy="200" r="150" fill="#547792" opacity="0.1" />
                
                <!-- Lingkaran Light (Light Blue) -->
                <circle cx="1200" cy="700" r="200" fill="#94B4C1" opacity="0.1" />
                
                <!-- Aksen Garis Melengkung -->
                <path d="M0,450 Q720,900 1440,450" fill="none" stroke="#547792" stroke-width="2" opacity="0.1" />
                <path d="M0,500 Q720,950 1440,500" fill="none" stroke="#94B4C1" stroke-width="2" opacity="0.2" />
            </svg>
        </div>

        <!-- Form Container (Centered, z-10) -->
        <div class="w-full max-w-md relative z-10">
            
            <!-- Logo Area (Responsive Text) -->
            <div class="mb-6 md:mb-10 flex flex-col items-center text-center">
                <h1 class="mt-4 text-2xl md:text-3xl font-bold text-[#213448]">SD Muhammadiyah Birrul Walidain</h1>
                <p class="text-[#547792] text-sm md:text-lg">Sistem Informasi Ekstrakurikuler</p>
            </div>

            <!-- Form (Responsive Padding & Radius) -->
            <form @submit.prevent="submit" class="w-full space-y-5 md:space-y-6 bg-white/30 backdrop-blur-sm p-6 md:p-8 rounded-xl md:rounded-2xl shadow-sm border border-white/40">
                
                <!-- Input Username -->
                <div class="flex flex-col space-y-2">
                    <label class="text-[#213448] font-semibold text-sm">Username</label>
                    <input 
                        type="text" 
                        v-model="form.username"
                        class="w-full px-4 py-3 rounded-lg border-2 border-[#94B4C1] bg-white/70 focus:bg-white text-[#213448] focus:outline-none focus:border-[#547792] transition-colors placeholder-[#94B4C1] text-sm md:text-base"
                        placeholder="Masukkan username anda"
                    >
                    <div v-if="form.errors.username" class="text-red-500 text-xs">
                        {{ form.errors.username }}
                    </div>
                </div>

                <!-- Input Password -->
                <div class="flex flex-col space-y-2">
                    <label class="text-[#213448] font-semibold text-sm">Password</label>
                    <div class="relative">
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            v-model="form.password"
                            class="w-full px-4 py-3 rounded-lg border-2 border-[#94B4C1] bg-white/70 focus:bg-white text-[#213448] focus:outline-none focus:border-[#547792] transition-colors placeholder-[#94B4C1] pr-12 text-sm md:text-base"
                            placeholder="••••••••"
                        >
                        
                        <!-- Tombol Mata (Hold to Show) -->
                        <button 
                            type="button"
                            @mousedown="showPassword = true" 
                            @mouseup="showPassword = false" 
                            @mouseleave="showPassword = false"
                            @touchstart.prevent="showPassword = true" 
                            @touchend.prevent="showPassword = false"
                            class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-[#547792] hover:text-[#213448] focus:outline-none transition-colors cursor-pointer select-none"
                            title="Tahan untuk melihat password"
                            style="-webkit-tap-highlight-color: transparent;"
                        >
                            <!-- Ikon Mata Terbuka -->
                            <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 md:w-6 md:h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <!-- Ikon Mata Tertutup -->
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 md:w-6 md:h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <div v-if="form.errors.password" class="text-red-500 text-xs">
                        {{ form.errors.password }}
                    </div>
                </div>

                <!-- Checkbox Remember Me (Tambahkan di sini sebelum tombol login) -->
                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            v-model="form.remember" 
                            class="rounded border-[#94B4C1] text-[#213448] shadow-sm focus:border-[#213448] focus:ring focus:ring-[#547792] focus:ring-opacity-50"
                        >
                        <span class="text-sm text-[#547792]">Ingat Saya</span>
                    </label>
                </div>

                <!-- Tombol Login -->
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full py-3 mt-4 bg-[#213448] text-[#EAE0CF] font-bold rounded-lg shadow-lg
                           transition-all duration-200 ease-in-out
                           hover:bg-[#547792] hover:shadow-xl hover:-translate-y-1
                           active:bg-[#1A2A3A] active:scale-95 active:shadow-none
                           disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-lg disabled:active:scale-100 text-sm md:text-base"
                >
                    <div class="flex items-center justify-center gap-2" v-if="form.processing">
                        <svg class="animate-spin h-5 w-5 text-[#EAE0CF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memproses...</span>
                    </div>
                    <span v-else>Masuk</span>
                </button>
            </form>
        </div>
    </div>
</template>