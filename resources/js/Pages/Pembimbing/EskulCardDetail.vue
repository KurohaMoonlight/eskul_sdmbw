<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import ModalFormJadwal from '@/Components/ModalFormJadwal.vue';
import ModalFormAddAnggota from '@/Components/ModalFormAddAnggota.vue';
import ModalFormKegiatan from '@/Components/ModalFormKegiatan.vue';
import ModalFormPrestasi from '@/Components/ModalFormPrestasi.vue'; 
import BoxAbsensi from '@/Components/BoxAbsensi.vue';
import BoxLogAbsensi from '@/Components/BoxLogAbsensi.vue';
import BoxPrestasi from '@/Components/BoxPrestasi.vue'; 
import BoxPenilaian from '@/Components/BoxPenilaian.vue'; 
import Footer from '../../Components/Footer.vue';
import { Calendar } from 'v-calendar';
import 'v-calendar/style.css';
import Swal from 'sweetalert2';

// 1. Definisikan Props
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
    kegiatan: {
        type: Array,
        default: () => []
    },
    logs: {
        type: Object,
        default: () => ({ data: [], links: [] }) 
    },
    logSummary: {
        type: Object,
        default: () => ({ total_pertemuan: 0, avg_kehadiran: 0, total_sakit: 0, total_izin: 0, total_alpha: 0 })
    },
    filters: Object,
    prestasi: {
        type: Array,
        default: () => []
    },
    nilai: { // Props untuk Nilai
        type: Array,
        default: () => []
    },
    currentSemesterInfo: Object, // Props info semester
    activeNilaiFilter: Object, // Props filter aktif
    existingAbsensi: { // Props untuk absensi hari ini
        type: Object,
        default: () => ({})
    }
});

// 2. State Management
const activeTab = ref('jadwal');
const showModalJadwal = ref(false);
const showModalAnggota = ref(false);
const showModalKegiatan = ref(false);
const showModalPrestasi = ref(false);

// State untuk Edit Data
const selectedJadwal = ref(null);
const selectedAnggota = ref(null); 
const selectedKegiatan = ref(null);
const selectedPrestasi = ref(null); 

// Helper untuk format tanggal YYYY-MM-DD lokal
const getLocalDateString = (date) => {
    const offset = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() - offset).toISOString().split('T')[0];
};

const selectedDate = ref(new Date());
const selectedDateStr = ref(getLocalDateString(new Date())); // Default hari ini string

// State untuk Kalender (Attributes kegiatan)
const calendarAttributes = computed(() => {
    return props.kegiatan.map(k => ({
        key: k.id_kegiatan,
        dot: 'blue',
        dates: new Date(k.tanggal),
        popover: {
            label: k.materi_kegiatan,
        },
        customData: k 
    }));
});

// Computed: Kegiatan pada tanggal yang dipilih
const activitiesOnSelectedDate = computed(() => {
    return props.kegiatan.filter(k => k.tanggal === selectedDateStr.value);
});

// --- METHOD JADWAL ---
const openAddJadwal = () => {
    selectedJadwal.value = null; // Reset form
    showModalJadwal.value = true;
};

const editJadwal = (item) => {
    selectedJadwal.value = item;
    showModalJadwal.value = true;
};

const deleteJadwal = (id) => {
    Swal.fire({
        title: 'Hapus Jadwal?',
        text: "Jadwal yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/admin/jadwal/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire('Terhapus!', 'Jadwal berhasil dihapus.', 'success');
                }
            });
        }
    });
};

// --- METHOD ANGGOTA ---
const deleteAnggota = (id) => {
    Swal.fire({
        title: 'Keluarkan Anggota?',
        text: "Siswa akan dihapus dari daftar anggota eskul ini.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Keluarkan!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/admin/anggota/${id}`);
        }
    });
};

const openAddAnggota = () => {
    selectedAnggota.value = null; // Reset form agar mode tambah
    showModalAnggota.value = true;
};

// --- METHOD KEGIATAN ---
const handleDayClick = (day) => {
    selectedDate.value = day.date;
    selectedDateStr.value = day.id; // day.id formatnya 'YYYY-MM-DD' dari v-calendar
    // Tidak langsung buka modal, tapi update list di bawahnya
};

const openAddKegiatan = () => {
    selectedKegiatan.value = null;
    showModalKegiatan.value = true;
};

const editKegiatan = (item) => {
    selectedKegiatan.value = item;
    showModalKegiatan.value = true;
};

const deleteKegiatan = (id) => {
    Swal.fire({
        title: 'Hapus Kegiatan?',
        text: "Data kegiatan beserta absensinya akan dihapus permanen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/admin/kegiatan/${id}`, {
                preserveScroll: true,
                onSuccess: () => Swal.fire('Terhapus!', 'Kegiatan berhasil dihapus.', 'success')
            });
        }
    });
};

// --- METHOD PRESTASI ---
const openAddPrestasi = () => {
    selectedPrestasi.value = null;
    showModalPrestasi.value = true;
};

const editPrestasi = (item) => {
    selectedPrestasi.value = item;
    showModalPrestasi.value = true;
};

const deletePrestasi = (id) => {
    Swal.fire({
        title: 'Hapus Prestasi?',
        text: "Data prestasi akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/admin/prestasi/${id}`, {
                preserveScroll: true,
                onSuccess: () => Swal.fire('Terhapus!', 'Data prestasi dihapus.', 'success')
            });
        }
    });
};

// --- UTILS ---
const formatTime = (time) => {
    return time ? time.substring(0, 5) : '-';
};

const formatDateIndo = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head :title="`Detail Eskul - ${props.eskul?.nama_eskul}`" />
    <Navbar />

    <div class="min-h-screen bg-[#FDFBF7] pb-20 pt-24 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- TOMBOL KEMBALI -->
            <div class="mb-6">
                <Link href="/pembimbing/dashboard" class="inline-flex items-center text-gray-500 hover:text-[#213448] font-bold transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Kembali ke Dashboard
                </Link>
            </div>

            <!-- HEADER INFO ESKUL -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100 mb-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-[#213448] opacity-5 rounded-bl-full -mr-10 -mt-10"></div>
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-3xl font-extrabold text-[#213448]">{{ props.eskul?.nama_eskul }}</h1>
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full border border-blue-100">Aktif</span>
                        </div>
                        <p class="text-gray-500 max-w-2xl leading-relaxed">{{ props.eskul?.deskripsi }}</p>
                        
                        <div class="flex flex-wrap gap-4 mt-6">
                            <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#547792]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Pembimbing: <span class="font-bold text-[#213448]">{{ props.eskul?.pembimbing?.nama_lengkap || '-' }}</span></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#547792]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Anggota: <span class="font-bold text-[#213448]">{{ props.anggota.length }} Siswa</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABS NAVIGATION (Scrollable on Mobile) -->
            <div class="flex overflow-x-auto gap-2 mb-8 border-b border-gray-200 pb-1 no-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0">
                <button 
                    v-for="tab in [
                        { id: 'jadwal', label: 'Jadwal & Kegiatan' },
                        { id: 'presensi', label: 'Presensi' },
                        { id: 'log', label: 'Riwayat Absensi' },
                        { id: 'prestasi', label: 'Prestasi' },
                        { id: 'penilaian', label: 'Penilaian Rapor' },
                        { id: 'anggota', label: 'Data Anggota' }
                    ]"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    class="whitespace-nowrap px-5 py-2.5 text-sm font-bold rounded-t-lg transition-all duration-300 border-b-2 flex-shrink-0"
                    :class="activeTab === tab.id ? 'text-[#213448] border-[#213448] bg-white shadow-sm' : 'text-gray-400 border-transparent hover:text-gray-600 hover:bg-gray-50'"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- TAB CONTENT -->
            <transition name="fade" mode="out-in">
                
                <!-- TAB 1: JADWAL & KEGIATAN -->
                <div v-if="activeTab === 'jadwal'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Kolom Kiri: Jadwal Rutin -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-bold text-[#213448] text-lg flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Jadwal Rutin
                                </h3>
                                <button @click="openAddJadwal" class="text-sm font-bold text-[#547792] hover:text-[#213448] transition">
                                    + Edit Jadwal
                                </button>
                            </div>
                            
                            <div v-if="props.jadwal.length > 0" class="space-y-3">
                                <div v-for="(item, index) in props.jadwal" :key="item.id_jadwal" 
                                    class="group flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 relative transition-all duration-200 hover:shadow-md hover:border-gray-200 gap-4">
                                    
                                    <!-- Info Jadwal -->
                                    <div class="flex items-start gap-4">
                                        <!-- Icon Hari -->
                                        <div class="w-12 h-12 rounded-xl bg-white flex flex-col items-center justify-center border border-gray-100 shadow-sm flex-shrink-0">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase">Hari</span>
                                            <span class="text-[#213448] font-extrabold text-sm uppercase">{{ item.hari.substring(0, 3) }}</span>
                                        </div>
                                        
                                        <!-- Detail Text -->
                                        <div class="space-y-1">
                                            <p class="font-bold text-[#213448] text-base">{{ item.hari }}</p>
                                            
                                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#547792]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ formatTime(item.jam_mulai) }} - {{ formatTime(item.jam_selesai) }}</span>
                                            </div>

                                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#547792]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span>{{ item.tempat || 'Lokasi belum diatur' }}</span>
                                            </div>

                                            <div class="flex items-center gap-2 text-xs font-bold text-[#547792] bg-blue-50 px-2 py-0.5 rounded w-fit mt-1">
                                                <span>Kelas: {{ props.eskul?.jenjang_kelas_min }} - {{ props.eskul?.jenjang_kelas_max }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tombol Aksi -->
                                    <div class="flex items-center gap-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity duration-200 self-end sm:self-center">
                                        <button 
                                            @click="editJadwal(item)"
                                            class="p-2 text-blue-600 bg-white border border-blue-100 hover:bg-blue-50 rounded-lg transition shadow-sm"
                                            title="Edit Jadwal"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button 
                                            @click="deleteJadwal(item.id_jadwal)"
                                            class="p-2 text-red-600 bg-white border border-red-100 hover:bg-red-50 rounded-lg transition shadow-sm"
                                            title="Hapus Jadwal"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>

                                </div>
                            </div>
                            <div v-else class="text-center py-6 text-gray-400 text-sm italic">
                                Belum ada jadwal rutin.
                            </div>
                        </div>

                        <!-- Card Quick Actions -->
                        <div class="bg-[#213448] rounded-xl p-6 text-white shadow-lg relative overflow-hidden group">
                             <div class="absolute top-0 right-0 w-24 h-24 bg-white opacity-10 rounded-bl-full -mr-5 -mt-5 transition-transform group-hover:scale-110"></div>
                             <h3 class="font-bold text-lg mb-2">Mulai Absensi?</h3>
                             <p class="text-gray-300 text-sm mb-4">Catat kehadiran siswa untuk kegiatan hari ini.</p>
                             <button @click="activeTab = 'presensi'" class="w-full py-2.5 bg-[#547792] hover:bg-[#4a6b85] rounded-lg font-bold text-sm transition shadow-md border border-white/10">
                                Buka Presensi
                             </button>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Kalender & Detail Kegiatan -->
                    <div class="lg:col-span-2 flex flex-col gap-6">
                        
                        <!-- CARD 1: Kalender -->
                        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                            <h3 class="font-bold text-[#213448] text-lg mb-6 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#547792]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Kalender Kegiatan
                            </h3>
                            
                            <div class="flex-grow min-h-[300px]">
                                <Calendar 
                                    expanded 
                                    transparent 
                                    borderless
                                    :attributes="calendarAttributes"
                                    @dayclick="handleDayClick"
                                    class="w-full h-full font-sans text-sm"
                                    color="blue"
                                />
                            </div>
                        </div>

                        <!-- CARD 2: Box Kegiatan (Detail) -->
                        <div class="bg-white p-6 rounded-xl border-l-4 border-[#213448] shadow-sm relative">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                                <div>
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Kegiatan Tanggal</span>
                                    <h3 class="font-extrabold text-[#213448] text-xl">{{ formatDateIndo(selectedDate) }}</h3>
                                </div>
                                
                                <button 
                                    @click="openAddKegiatan"
                                    class="px-4 py-2 bg-[#213448] text-[#EAE0CF] rounded-lg text-sm font-bold hover:bg-[#1a2a3a] transition shadow-md flex items-center gap-2"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    Tambah Kegiatan
                                </button>
                            </div>

                            <!-- List Kegiatan pada Tanggal Terpilih -->
                            <div v-if="activitiesOnSelectedDate.length > 0" class="space-y-3">
                                <div v-for="kegiatan in activitiesOnSelectedDate" :key="kegiatan.id_kegiatan" 
                                    class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100 hover:shadow-sm transition group"
                                >
                                    <div>
                                        <p class="font-bold text-gray-800 text-base mb-1">{{ kegiatan.materi_kegiatan }}</p>
                                        <div class="flex items-center gap-3 text-sm text-gray-500">
                                            <span v-if="kegiatan.jam_mulai" class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#547792]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ formatTime(kegiatan.jam_mulai) }} - {{ formatTime(kegiatan.jam_selesai) }}
                                            </span>
                                            <!-- PERBAIKAN: Mengganti teks 'Waktu belum diatur' jadi 'Catatan Pembimbing' -->
                                            <span v-else class="italic text-gray-400">Catatan Pembimbing</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <button 
                                            @click="editKegiatan(kegiatan)"
                                            class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                                            title="Edit Kegiatan"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button 
                                            @click="deleteKegiatan(kegiatan.id_kegiatan)"
                                            class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition"
                                            title="Hapus Kegiatan"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="text-center py-8 bg-gray-50/50 rounded-lg border border-dashed border-gray-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-gray-500 font-medium">Tidak ada kegiatan pada tanggal ini.</p>
                                <p class="text-xs text-gray-400 mt-1">Klik tombol tambah untuk menjadwalkan kegiatan.</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- TAB 2: PRESENSI (BOX ABSENSI) -->
                <div v-else-if="activeTab === 'presensi'">
                    <BoxAbsensi 
                        :jadwal="props.jadwal" 
                        :anggota="props.anggota" 
                        :idEskul="props.eskul?.id_eskul"
                        :existingAbsensi="props.existingAbsensi" 
                    />
                </div>

                <!-- TAB 3: LOG ABSENSI -->
                <div v-else-if="activeTab === 'log'">
                    <BoxLogAbsensi 
                        :logs="props.logs" 
                        :summary="props.logSummary"
                        :filters="props.filters"
                        :idEskul="props.eskul?.id_eskul"
                    />
                </div>

                 <!-- TAB 4: PRESTASI -->
                 <div v-else-if="activeTab === 'prestasi'">
                    <BoxPrestasi 
                        :prestasi="props.prestasi" 
                        @add="openAddPrestasi"
                        @edit="editPrestasi"
                        @delete="deletePrestasi"
                    />
                </div>

                <!-- TAB 5: PENILAIAN -->
                <div v-else-if="activeTab === 'penilaian'">
                    <BoxPenilaian 
                        :nilai="props.nilai"
                        :idEskul="props.eskul?.id_eskul"
                        :currentSemesterInfo="props.currentSemesterInfo"
                        :activeNilaiFilter="props.activeNilaiFilter"
                    />
                </div>

                <!-- TAB 6: ANGGOTA -->
                <div v-else-if="activeTab === 'anggota'">
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="font-bold text-[#213448] text-lg">Daftar Anggota</h3>
                                <p class="text-sm text-gray-500">Kelola siswa yang terdaftar di ekstrakurikuler ini.</p>
                            </div>
                            <button @click="openAddAnggota" class="px-4 py-2 bg-[#213448] text-[#EAE0CF] rounded-lg text-sm font-bold hover:bg-[#1a2a3a] transition shadow-md">
                                + Tambah Anggota
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
                                    <tr>
                                        <th class="p-4 border-b border-gray-100">No</th>
                                        <th class="p-4 border-b border-gray-100">Nama Siswa</th>
                                        <th class="p-4 border-b border-gray-100">Kelas</th>
                                        <th class="p-4 border-b border-gray-100 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="(item, index) in props.anggota" :key="item.id_anggota_eskul" class="hover:bg-gray-50 transition">
                                        <td class="p-4 text-gray-400 text-sm font-bold">{{ index + 1 }}</td>
                                        <td class="p-4">
                                            <p class="font-bold text-[#213448]">{{ item.peserta?.nama_lengkap }}</p>
                                            <p class="text-xs text-gray-500">NIS: {{ item.peserta?.nis || '-' }}</p>
                                        </td>
                                        <td class="p-4">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-bold">{{ item.peserta?.tingkat_kelas }}</span>
                                        </td>
                                        <td class="p-4 text-center">
                                            <button @click="deleteAnggota(item.id_anggota_eskul)" class="text-red-500 hover:text-red-700 text-xs font-bold px-3 py-1.5 bg-red-50 hover:bg-red-100 rounded-lg transition">
                                                Keluarkan
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="props.anggota.length === 0">
                                        <td colspan="4" class="p-8 text-center text-gray-400 italic">Belum ada anggota terdaftar.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </transition>
        </div>

        <!-- MODALS -->
        <ModalFormJadwal 
            :show="showModalJadwal" 
            :idEskul="props.eskul?.id_eskul" 
            :jadwalData="selectedJadwal" 
            :minLimit="props.eskul?.jenjang_kelas_min"
            :maxLimit="props.eskul?.jenjang_kelas_max"
            @close="showModalJadwal = false" 
        />
        <ModalFormAddAnggota 
            :show="showModalAnggota" 
            :idEskul="props.eskul?.id_eskul" 
            :anggotaData="selectedAnggota"
            :minKelas="props.eskul?.jenjang_kelas_min"
            :maxKelas="props.eskul?.jenjang_kelas_max"
            @close="showModalAnggota = false" 
         />
        <ModalFormKegiatan 
            :show="showModalKegiatan" 
            :idEskul="props.eskul?.id_eskul" 
            :selectedDate="selectedDate" 
            :kegiatanData="selectedKegiatan" 
            @close="showModalKegiatan = false" 
        />
        
        <!-- Modal Prestasi Baru -->
        <ModalFormPrestasi 
            :show="showModalPrestasi" 
            :idEskul="props.eskul?.id_eskul" 
            :prestasiData="selectedPrestasi" 
            :anggota="props.anggota"
            @close="showModalPrestasi = false" 
        />
    </div>
    <Footer />
</template>

<style>
/* Hide Scrollbar for Tabs */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Override warna default v-calendar agar sesuai tema */
.vc-blue {
    --vc-accent-50: #f0f9ff;
    --vc-accent-100: #e0f2fe;
    --vc-accent-200: #bae6fd;
    --vc-accent-300: #7dd3fc;
    --vc-accent-400: #38bdf8;
    --vc-accent-500: #0ea5e9;
    --vc-accent-600: #547792; /* Warna Tema Utama */
    --vc-accent-700: #0369a1;
    --vc-accent-800: #075985;
    --vc-accent-900: #0c4a6e;
}

/* Transisi Tab */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>