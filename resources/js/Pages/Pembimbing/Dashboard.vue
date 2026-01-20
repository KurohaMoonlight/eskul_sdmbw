<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import CardShowEskul from '@/Components/CardShowEskul.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const props = defineProps({
    eskul_list: {
        type: Array, // Menerima array list eskul
        default: () => []
    }
});

const truncatedName = computed(() => {
    const name = user.value?.nama_lengkap || 'Pembimbing';
    return name.length > 25 ? name.substring(0, 25) + '...' : name;
});

const today = new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
</script>

<template>
    <Head title="Dashboard Pembimbing" />

    <div class="min-h-screen bg-gray-50/50">
        <Navbar />

        <main class="py-10">
            <div class="w-full px-6 md:px-8">
                
                <div class="flex flex-col items-start justify-start text-left mb-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">
                        {{ today }}
                    </p>

                    <h1 class="text-3xl font-bold text-gray-800 tracking-tight md:text-4xl">
                        Selamat Datang, <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#213448] to-[#547792]">
                            {{ truncatedName }}
                        </span>
                    </h1>
                </div>

                <div class="w-full h-px bg-gradient-to-r from-gray-300 via-gray-200 to-transparent my-8"></div>

                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#547792]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Daftar Ekstrakurikuler Anda
                    </h2>

                    <div v-if="props.eskul_list.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <CardEskul 
                            v-for="item in props.eskul_list" 
                            :key="item.id_eskul" 
                            :data="item" 
                        />
                    </div>

                    <div v-else class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-10 text-center">
                        <p class="text-gray-500 text-sm">Anda belum mengampu ekstrakurikuler apapun.</p>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>