<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import ModalFormJadwal from '@/Components/ModalFormJadwal.vue';
import ModalFormAddAnggota from '@/Components/ModalFormAddAnggota.vue'; // Gunakan Modal Anda

const props = defineProps({
    eskul: Object,
    jadwal: {
        type: Array,
        default: () => []
    },
    anggota: {
        type: Array,
        default: () => []
    },
});

const formatTime = (time) => {
    if (!time) return '-';
    return time.substring(0, 5);
};

// State untuk Modal
const showModalJadwal = ref(false);
const showModalAnggota = ref(false);

// State untuk Data Edit (Jika nanti butuh edit, sementara null)
const selectedAnggota = ref(null);

const openTambahAnggota = () => {
    selectedAnggota.value = null; // Pastikan null saat tambah baru
    showModalAnggota.value = true;
};

// Jika nanti butuh edit
const openEditAnggota = (item) => {
    selectedAnggota.value = item;
    showModalAnggota.value = true;
};
</script>

<template>
    <Head :title="`Detail ${props.eskul?.nama_eskul || 'Eskul'}`" />

    <div class="min-h-screen bg-gray-50/50">
        <Navbar />

        <main class="py-10">
            <div class="w-full px-6 md:px-8">
                
                <div class="mb-6">
                    <Link href="/pembimbing/dashboard" class="text-sm text-gray-500 hover:text-[#547792] flex items-center gap-1 mb-2 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Kembali ke Dashboard
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
                        {{ props.eskul?.nama_eskul }}
                    </h1>
                </div>

                <div class="flex flex-col lg:flex-row gap-6 w-full">
                    
                    <!-- BOX JADWAL (Kiri) -->
                    <div class="w-full lg:w-[40%]">
                        <div class="rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden h-full flex flex-col">
                            <div class="bg-[#213448] px-6 py-4 flex items-center justify-between">
                                <h3 class="text-[#EAE0CF] font-bold text-lg flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Jadwal Latihan
                                </h3>
                                <span class="bg-[#94B4C1]/20 text-[#94B4C1] text-xs px-2 py-1 rounded-md font-medium">
                                    {{ props.jadwal.length }} Sesi
                                </span>
                            </div>

                            <div class="flex-1 flex flex-col max-h-[500px] overflow-y-auto">
                                <div v-if="props.jadwal.length > 0" class="divide-y divide-gray-100">
                                    <div v-for="item in props.jadwal" :key="item.id_jadwal" class="p-4 hover:bg-gray-50 transition-colors flex items-center gap-4">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#213448]/5 text-[#213448]">
                                            <span class="text-xs font-bold uppercase">{{ item.hari ? item.hari.substring(0, 3) : '?' }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-800 truncate">{{ item.lokasi || 'Lokasi belum diatur' }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ formatTime(item.jam_mulai) }} - {{ formatTime(item.jam_selesai) }} WIB</p>
                                        </div>
                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-[10px] font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                            Kls {{ item.kelas_min }}-{{ item.kelas_max }}
                                        </span>
                                    </div>
                                </div>
                                <div v-else class="p-8 flex flex-col justify-center items-center text-center h-full min-h-[150px]">
                                    <p class="text-gray-400 text-sm">Belum ada jadwal latihan.</p>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 mt-auto">
                                <button @click="showModalJadwal = true" class="w-full rounded-lg bg-[#547792] px-4 py-2 text-sm font-bold text-white hover:bg-[#213448] shadow-md transition flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    Tambah Jadwal
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- BOX ANGGOTA (Kanan) -->
                    <div class="w-full lg:w-[60%]">
                        <div class="rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden h-full flex flex-col">
                            <div class="bg-[#213448] px-6 py-4 flex items-center justify-between">
                                <h3 class="text-[#EAE0CF] font-bold text-lg flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    Daftar Anggota
                                </h3>
                                <span class="bg-[#94B4C1]/20 text-[#94B4C1] text-xs px-2 py-1 rounded-md font-medium">
                                    {{ props.anggota.length }} Peserta
                                </span>
                            </div>

                            <div class="flex-1 flex flex-col max-h-[500px] overflow-y-auto">
                                <div v-if="props.anggota.length > 0" class="divide-y divide-gray-100">
                                    <div v-for="item in props.anggota" :key="item.id_anggota" class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#547792]/10 text-[#547792] font-bold border border-[#547792]/20 uppercase">
                                                {{ item.peserta?.nama_lengkap?.charAt(0) || '?' }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-800">{{ item.peserta?.nama_lengkap || 'Tanpa Nama' }}</p>
                                                <p class="text-xs text-gray-500">
                                                    Kelas {{ item.peserta?.tingkat_kelas || '-' }} • {{ item.peserta?.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} • {{ item.tahun_ajaran }}
                                                </p>
                                            </div>
                                        </div>
                                        <span 
                                            :class="item.status_aktif ? 'bg-emerald-50 text-emerald-600 ring-emerald-600/20' : 'bg-red-50 text-red-600 ring-red-600/20'" 
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-medium ring-1 ring-inset"
                                        >
                                            {{ item.status_aktif ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </div>
                                </div>
                                <div v-else class="p-8 flex flex-col justify-center items-center text-center h-full min-h-[150px]">
                                    <p class="text-gray-400 text-sm">Belum ada anggota terdaftar.</p>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 mt-auto">
                                <button @click="openTambahAnggota" class="w-full rounded-lg bg-white border border-[#94B4C1] px-4 py-2 text-sm font-bold text-[#547792] hover:bg-[#547792] hover:text-white shadow-sm transition flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                    Tambah Anggota
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <ModalFormJadwal 
            v-if="showModalJadwal"
            :show="showModalJadwal"
            :jadwalData="null" 
            :idEskul="props.eskul?.id_eskul" 
            @close="showModalJadwal = false"
        />

        <!-- Menggunakan Modal Anda -->
        <ModalFormAddAnggota 
            v-if="showModalAnggota"
            :show="showModalAnggota"
            :idEskul="props.eskul?.id_eskul"
            :anggotaData="selectedAnggota"
            @close="showModalAnggota = false"
        />
    </div>
</template>