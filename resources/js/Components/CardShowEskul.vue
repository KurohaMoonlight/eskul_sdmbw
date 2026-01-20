<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    data: Object, // Menerima object eskul tunggal
});

const jenjang = (min, max) => {
    if (min === max) return `Kelas ${min}`;
    return `Kelas ${min} - ${max}`;
};
</script>

<template>
    <div class="group relative flex flex-col justify-between overflow-hidden rounded-xl bg-white border border-gray-100 shadow-sm transition-all hover:shadow-md hover:border-[#547792]/30">
        <div class="absolute top-0 left-0 h-1.5 w-full bg-gradient-to-r from-[#213448] to-[#547792]"></div>

        <div class="p-5 flex-1">
            <div class="flex items-start justify-between mb-3">
                <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-[#547792]/10 text-[#547792]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                    {{ jenjang(data.jenjang_kelas_min, data.jenjang_kelas_max) }}
                </span>
            </div>

            <h3 class="text-lg font-bold text-gray-800 mb-1 group-hover:text-[#547792] transition-colors">
                {{ data.nama_eskul }}
            </h3>
            
            <p class="text-sm text-gray-500 line-clamp-2">
                {{ data.deskripsi || 'Tidak ada deskripsi.' }}
            </p>
        </div>

        <div class="bg-gray-50 px-5 py-3 border-t border-gray-100 flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Extracurricular
            </span>
            <Link :href="`/pembimbing/eskul/${data.id_eskul}`" class="text-sm font-bold text-[#547792] hover:text-[#213448] flex items-center gap-1">
                Kelola
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </Link>
        </div>
    </div>
</template>