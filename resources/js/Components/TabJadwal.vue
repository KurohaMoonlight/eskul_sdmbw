<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ModalFormJadwal from '@/Components/ModalFormJadwal.vue';

const props = defineProps({
    jadwal: {
        type: Array,
        default: () => [],
    },
    idEskul: {
        type: [Number, String],
        required: true
    },
    limitMin: [Number, String],
    limitMax: [Number, String]
});

// State untuk Modal
const showModal = ref(false);
const selectedJadwal = ref(null);

// Fungsi Buka Modal Tambah
const openTambah = () => {
    selectedJadwal.value = null; // Reset data agar form kosong
    showModal.value = true;
};

// Fungsi Buka Modal Edit
const openEdit = (item) => {
    selectedJadwal.value = item; // Isi data ke modal untuk diedit
    showModal.value = true;
};

// Fungsi Hapus Jadwal
const deleteJadwal = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus jadwal latihan ini? Data yang dihapus tidak dapat dikembalikan.')) {
        router.delete(`/admin/jadwal/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                // Opsional: Tambahkan notifikasi toast jika ada library toast
            }
        });
    }
};

// Helper warna badge hari
const getDayColor = (day) => {
    const colors = {
        'Senin': 'bg-red-100 text-red-700 border-red-200',
        'Selasa': 'bg-orange-100 text-orange-700 border-orange-200',
        'Rabu': 'bg-yellow-100 text-yellow-700 border-yellow-200',
        'Kamis': 'bg-green-100 text-green-700 border-green-200',
        'Jumat': 'bg-cyan-100 text-cyan-700 border-cyan-200',
        'Sabtu': 'bg-blue-100 text-blue-700 border-blue-200',
        'Minggu': 'bg-purple-100 text-purple-700 border-purple-200',
    };
    return colors[day] || 'bg-gray-100 text-gray-700 border-gray-200';
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold text-[#213448]">Jadwal Latihan</h3>
                <p class="text-sm text-[#94B4C1]">Atur waktu dan lokasi kegiatan ekstrakurikuler.</p>
            </div>
            
            <button 
                @click="openTambah"
                class="group flex items-center justify-center gap-2 rounded-xl bg-[#213448] px-5 py-2.5 text-sm font-bold text-[#EAE0CF] shadow-lg transition-all hover:bg-[#547792] hover:shadow-xl active:scale-95"
            >
                <svg class="h-5 w-5 transition-transform group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Jadwal</span>
            </button>
        </div>

        <div v-if="jadwal && jadwal.length > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div 
                v-for="item in jadwal" 
                :key="item.id_jadwal"
                class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-[#94B4C1]/30 bg-white p-5 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg"
            >
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-[#EAE0CF]/30 transition-all group-hover:scale-150"></div>

                <div class="relative z-10">
                    <div class="flex items-start justify-between">
                        <span 
                            class="rounded-lg border px-3 py-1 text-xs font-bold uppercase tracking-wide"
                            :class="getDayColor(item.hari)"
                        >
                            {{ item.hari }}
                        </span>
                        
                        <div class="flex gap-2">
                            <button 
                                @click="openEdit(item)"
                                class="rounded-lg p-1.5 text-[#94B4C1] transition-colors hover:bg-[#547792] hover:text-white"
                                title="Edit Jadwal"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button 
                                @click="deleteJadwal(item.id_jadwal)"
                                class="rounded-lg p-1.5 text-[#94B4C1] transition-colors hover:bg-red-500 hover:text-white"
                                title="Hapus Jadwal"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="flex items-center gap-3 text-[#213448]">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#EAE0CF]/50 text-[#547792]">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-[#94B4C1]">Waktu</p>
                                <p class="text-sm font-bold">{{ item.jam_mulai }} - {{ item.jam_selesai }} WIB</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-[#213448]">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#EAE0CF]/50 text-[#547792]">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-[#94B4C1]">Lokasi</p>
                                <p class="text-sm font-bold truncate max-w-[150px]" :title="item.lokasi">{{ item.lokasi }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 border-t border-[#94B4C1]/20 pt-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-[#94B4C1]">Peserta:</span>
                        <span class="inline-flex items-center rounded-md bg-[#213448] px-2 py-1 text-xs font-bold text-[#EAE0CF]">
                            Kelas {{ item.kelas_min }} - {{ item.kelas_max }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#94B4C1]/30 py-16 text-center">
            <div class="rounded-full bg-[#F3F4F6] p-4">
                <svg class="h-8 w-8 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-bold text-[#213448]">Belum ada jadwal</h3>
            <p class="text-sm text-[#94B4C1]">Silakan tambahkan jadwal latihan baru.</p>
            <button 
                @click="openTambah"
                class="mt-4 text-sm font-bold text-[#547792] hover:text-[#213448] hover:underline"
            >
                + Tambah Sekarang
            </button>
        </div>

        <ModalFormJadwal 
            :show="showModal"
            :jadwalData="selectedJadwal"
            :idEskul="idEskul"
            :minLimit="limitMin"
            :maxLimit="limitMax"
            @close="showModal = false"
        />
    </div>
</template>