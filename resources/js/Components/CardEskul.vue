<script setup>
import { computed } from 'vue';

const props = defineProps({
    eskul: {
        type: Object,
        required: true,
    },
});

defineEmits(['click']);

// Palette warna untuk strip kiri (Warna-warni agar terlihat 'random')
const colors = [
    'bg-red-500',
    'bg-orange-500',
    'bg-amber-500',
    'bg-green-500',
    'bg-emerald-500',
    'bg-teal-500',
    'bg-cyan-500',
    'bg-sky-500',
    'bg-blue-500',
    'bg-indigo-500',
    'bg-violet-500',
    'bg-purple-500',
    'bg-fuchsia-500',
    'bg-pink-500',
    'bg-rose-500',
];

// Menentukan warna berdasarkan ID Eskul.
const accentColor = computed(() => {
    if (props.eskul.id_eskul) {
        return colors[props.eskul.id_eskul % colors.length];
    }
    return colors[Math.floor(Math.random() * colors.length)];
});
</script>

<template>
    <div 
        @click="$emit('click')"
        class="group relative flex w-full cursor-pointer flex-col overflow-hidden rounded-lg bg-white shadow-md transition-all hover:-translate-y-1 hover:shadow-xl border border-[#94B4C1]/30"
    >
        <!-- Strip Warna Kiri (Random) -->
        <div :class="[accentColor, 'absolute left-0 top-0 bottom-0 w-2']"></div>

        <!-- Konten Card -->
        <div class="flex flex-col p-5 pl-7 h-full"> <!-- pl-7 memberi jarak dari strip warna -->
            
            <!-- Header: Nama Eskul -->
            <div class="mb-2 flex items-start justify-between">
                <h3 class="text-lg font-bold text-[#213448] group-hover:text-[#547792] transition-colors line-clamp-1">
                    {{ eskul.nama_eskul }}
                </h3>
            </div>

            <!-- Jenjang Kelas (Badge) -->
            <div class="mb-3">
                <span class="inline-block rounded bg-[#EAE0CF] px-2 py-1 text-xs font-bold text-[#213448]">
                    Kelas {{ eskul.jenjang_kelas_min }} - {{ eskul.jenjang_kelas_max }}
                </span>
            </div>

            <!-- Pembimbing -->
            <div class="mb-2 text-sm text-[#547792]">
                <span class="font-semibold text-[#213448]">Pembimbing:</span> 
                <!-- Menampilkan Multi Pembimbing (Array) atau Single (Fallback) -->
                <span class="line-clamp-1">
                    {{ eskul.pembimbings && eskul.pembimbings.length > 0 
                        ? eskul.pembimbings.map(p => p.nama_lengkap).join(', ') 
                        : (eskul.pembimbing ? eskul.pembimbing.nama_lengkap : '-') 
                    }}
                </span>
            </div>

            <!-- Deskripsi (Terpotong / Line Clamp) -->
            <p class="text-sm text-gray-500 line-clamp-3 mt-auto">
                {{ eskul.deskripsi || 'Belum ada deskripsi.' }}
            </p>
        </div>
    </div>
</template>