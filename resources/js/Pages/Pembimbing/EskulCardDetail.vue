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
import BoxPenilaian from '@/Components/BoxPenilaian.vue'; // Import Box Penilaian
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
    filters: {
        type: Object,
        default: () => ({ search: '', start_date: '', end_date: '', status: '' })
    },
    prestasi: {
        type: Array,
        default: () => []
    },
    // Props untuk Penilaian
    nilai: {
        type: Array,
        default: () => []
    },
    currentSemesterInfo: {
        type: Object,
        default: () => ({ semester: 'Ganjil', tahun: '2025/2026' })
    }
});

const formatTime = (time) => {
    if (!time) return '-';
    return time.substring(0, 5);
};

// ... (State Modal & Calendar sama seperti sebelumnya) ...
const showModalJadwal = ref(false);
const showModalAnggota = ref(false);
const showModalKegiatan = ref(false);
const showModalPrestasi = ref(false); 

const selectedAnggota = ref(null);
const selectedKegiatan = ref(null);
const selectedPrestasi = ref(null); 
const selectedDate = ref(new Date());

const calendarAttributes = computed(() => {
    const attrs = [
        {
            key: 'today',
            highlight: { color: 'blue', fillMode: 'light' },
            dates: new Date(),
        },
    ];

    if (props.kegiatan && props.kegiatan.length > 0) {
        props.kegiatan
            .filter(k => props.eskul && k.id_eskul === props.eskul.id_eskul)
            .forEach(k => {
                const dateKegiatan = new Date(k.tanggal);
                const today = new Date();
                dateKegiatan.setHours(0, 0, 0, 0);
                today.setHours(0, 0, 0, 0);

                let dotColor = 'red';
                if (dateKegiatan < today) dotColor = 'blue';
                else if (dateKegiatan.getTime() === today.getTime()) dotColor = 'green';

                attrs.push({
                    key: `kegiatan-${k.id_kegiatan}`,
                    dot: { color: dotColor, class: 'kegiatan-dot' },
                    dates: new Date(k.tanggal),
                    customData: k,
                });
            });
    }
    return attrs;
});

const kegiatanHariIni = computed(() => {
    if (!selectedDate.value) return [];
    const year = selectedDate.value.getFullYear();
    const month = String(selectedDate.value.getMonth() + 1).padStart(2, '0');
    const day = String(selectedDate.value.getDate()).padStart(2, '0');
    const dateStr = `${year}-${month}-${day}`;

    return props.kegiatan.filter(k => 
        k.tanggal === dateStr && 
        props.eskul && 
        k.id_eskul === props.eskul.id_eskul
    );
});

const openTambahAnggota = () => { selectedAnggota.value = null; showModalAnggota.value = true; };
const openEditAnggota = (anggota) => { selectedAnggota.value = anggota; showModalAnggota.value = true; };
const deleteForm = useForm({});
const deleteAnggota = (id) => {
    Swal.fire({
        title: 'Hapus Anggota?',
        text: "Data keanggotaan dan data siswa akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteForm.delete(`/admin/anggota/${id}`, {
                preserveScroll: true,
                onSuccess: () => Swal.fire('Terhapus!', 'Anggota berhasil dihapus.', 'success'),
                onError: () => Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus.', 'error')
            });
        }
    });
};

const updateStatusAnggota = (anggota) => {
    const newStatus = !anggota.status_aktif;
    router.put(`/admin/anggota/${anggota.id_anggota}`, {
        nama_lengkap: anggota.peserta.nama_lengkap,
        tingkat_kelas: anggota.peserta.tingkat_kelas,
        jenis_kelamin: anggota.peserta.jenis_kelamin,
        tahun_ajaran: anggota.tahun_ajaran,
        status_aktif: newStatus, 
    }, {
        preserveScroll: true,
        onSuccess: () => {
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                timerProgressBar: true, didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            Toast.fire({ icon: 'success', title: `Status anggota diubah: ${newStatus ? 'Aktif' : 'Non-Aktif'}` });
        },
        onError: () => {
            Swal.fire('Gagal!', 'Gagal mengubah status.', 'error');
        }
    });
};

const openTambahKegiatan = () => { selectedKegiatan.value = null; showModalKegiatan.value = true; };
const openEditKegiatan = (item) => { selectedKegiatan.value = item; showModalKegiatan.value = true; };
const deleteKegiatan = (id) => {
    Swal.fire({
        title: 'Hapus Kegiatan?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteForm.delete(`/admin/kegiatan/${id}`, {
                preserveScroll: true,
                onSuccess: () => Swal.fire('Terhapus!', 'Data kegiatan berhasil dihapus.', 'success'),
                onError: () => Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error')
            });
        }
    });
};

const onDayClick = (day) => { selectedDate.value = day.date; };

// Logic Prestasi
const openTambahPrestasi = () => {
    selectedPrestasi.value = null;
    showModalPrestasi.value = true;
};
const openEditPrestasi = (item) => {
    selectedPrestasi.value = item;
    showModalPrestasi.value = true;
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

                <div class="flex flex-col gap-6 w-full">
                    
                    <!-- ROW 1: Grid 2 Kolom -->
                    <div class="flex flex-col lg:flex-row gap-6">
                        <!-- KOLOM KIRI (Jadwal + Agenda) -->
                        <div class="w-full lg:w-[40%] flex flex-col gap-6">
                            <!-- 1. BOX JADWAL -->
                            <div class="rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                                <div class="bg-[#213448] px-6 py-4 flex items-center justify-between">
                                    <h3 class="text-[#EAE0CF] font-bold text-lg flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        Jadwal Latihan
                                    </h3>
                                    <span class="bg-[#94B4C1]/20 text-[#94B4C1] text-xs px-2 py-1 rounded-md font-medium">
                                        {{ props.jadwal.length }} Sesi
                                    </span>
                                </div>
                                <div class="flex-1 flex flex-col max-h-[300px] overflow-y-auto">
                                    <div v-if="props.jadwal.length > 0" class="divide-y divide-gray-100">
                                        <div v-for="item in props.jadwal" :key="item.id_jadwal" class="p-4 hover:bg-gray-50 transition-colors flex items-center gap-4">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#213448]/5 text-[#213448]">
                                                <span class="text-xs font-bold uppercase">{{ item.hari ? item.hari.substring(0, 3) : '?' }}</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-800 truncate">{{ item.lokasi || 'Lokasi belum diatur' }}</p>
                                                <p class="text-xs text-gray-500 mt-0.5">{{ formatTime(item.jam_mulai) }} - {{ formatTime(item.jam_selesai) }} WIB</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="p-6 flex flex-col justify-center items-center text-center">
                                        <p class="text-gray-400 text-sm">Belum ada jadwal latihan.</p>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100">
                                    <button @click="showModalJadwal = true" class="w-full rounded-lg bg-[#547792] px-4 py-2 text-sm font-bold text-white hover:bg-[#213448] shadow-md transition flex items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Tambah Jadwal
                                    </button>
                                </div>
                            </div>

                            <!-- 2. BOX AGENDA & KALENDER -->
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                                <h3 class="text-[#213448] font-bold text-lg mb-4 flex items-center gap-2 pb-2 border-b border-gray-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#547792]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Agenda Kegiatan
                                </h3>
                                <div class="flex flex-col xl:flex-row gap-6">
                                    <div class="flex-shrink-0 flex justify-center">
                                        <Calendar expanded transparent borderless :attributes="calendarAttributes" @dayclick="onDayClick" class="custom-calendar w-full max-w-[300px]" />
                                    </div>
                                    <div class="flex-1 border-l border-gray-100 pl-0 xl:pl-6 pt-4 xl:pt-0 flex flex-col">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="text-sm font-bold text-gray-700">
                                                {{ selectedDate ? selectedDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : 'Pilih Tanggal' }}
                                            </h4>
                                            <button @click="openTambahKegiatan" class="text-xs bg-[#547792] text-white px-2 py-1 rounded hover:bg-[#213448] transition flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                Kegiatan
                                            </button>
                                        </div>
                                        <div class="flex-1 bg-gray-50 rounded-lg p-3 min-h-[150px] max-h-[300px] overflow-y-auto">
                                            <template v-if="kegiatanHariIni.length > 0">
                                                <div v-for="kegiatan in kegiatanHariIni" :key="kegiatan.id_kegiatan" class="mb-3 bg-white p-3 rounded shadow-sm border border-gray-100 last:mb-0 relative group/item">
                                                    <div class="absolute top-2 right-2 flex gap-1 opacity-100 md:opacity-0 md:group-hover/item:opacity-100 transition-opacity bg-white p-1 rounded-md shadow-sm border border-gray-100">
                                                        <button @click="openEditKegiatan(kegiatan)" class="p-1 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded" title="Edit">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                        </button>
                                                        <button @click="deleteKegiatan(kegiatan.id_kegiatan)" class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded" title="Hapus">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    </div>
                                                    <div class="flex justify-between items-start pr-14">
                                                        <h5 class="text-sm font-bold text-[#213448] mb-1">Materi</h5>
                                                    </div>
                                                    <p class="text-xs text-gray-600 mb-2 whitespace-pre-wrap">{{ kegiatan.materi_kegiatan }}</p>
                                                    <div v-if="kegiatan.catatan_pembimbing" class="mt-2 pt-2 border-t border-gray-50">
                                                        <span class="text-[10px] uppercase font-bold text-gray-400">Catatan:</span>
                                                        <p class="text-xs text-gray-500 italic">"{{ kegiatan.catatan_pembimbing }}"</p>
                                                    </div>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <div class="flex flex-col justify-center items-center text-center h-full min-h-[120px]">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                    <p class="text-xs text-gray-400">Tidak ada kegiatan pada tanggal ini.</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM KANAN (ANGGOTA) -->
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
                                <div class="flex-1 flex flex-col max-h-[600px] overflow-y-auto">
                                    <div v-if="props.anggota.length > 0" class="divide-y divide-gray-100">
                                        <div v-for="item in props.anggota" :key="item.id_anggota" class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between group/anggota">
                                            <div class="flex items-center gap-4">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#547792]/10 text-[#547792] font-bold border border-[#547792]/20 uppercase">
                                                    {{ item.peserta?.nama_lengkap?.charAt(0) || '?' }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-gray-800">{{ item.peserta?.nama_lengkap || 'Tanpa Nama' }}</p>
                                                    <p class="text-xs text-gray-500">Kelas {{ item.peserta?.tingkat_kelas || '-' }} • {{ item.peserta?.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} • {{ item.tahun_ajaran }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <label class="relative inline-flex items-center cursor-pointer" title="Ubah Status">
                                                    <input type="checkbox" class="sr-only peer" :checked="item.status_aktif" @change="updateStatusAnggota(item)">
                                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-200 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                                </label>
                                                <button @click="openEditAnggota(item)" class="text-gray-400 hover:text-blue-500 transition opacity-100 md:opacity-0 md:group-hover/anggota:opacity-100" title="Edit Data">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                </button>
                                                <button @click="deleteAnggota(item.id_anggota)" class="text-gray-400 hover:text-red-500 transition opacity-100 md:opacity-0 md:group-hover/anggota:opacity-100" title="Hapus Anggota">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
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

                    <!-- ROW 2: BOX ABSENSI (Full Width) -->
                    <div class="w-full">
                        <BoxAbsensi 
                            :jadwal="props.jadwal"
                            :anggota="props.anggota"
                            :selectedDate="selectedDate"
                        />
                    </div>

                    <!-- ROW 3: BOX LOG ABSENSI -->
                    <div class="w-full">
                        <BoxLogAbsensi 
                            :logs="props.logs"
                            :summary="props.logSummary"
                            :filters="props.filters"
                            :idEskul="props.eskul?.id_eskul"
                        />
                    </div>

                    <!-- ROW 4: BOX PRESTASI (Di Bawah Log) -->
                    <div class="w-full">
                        <BoxPrestasi 
                            :prestasi="props.prestasi"
                            :idEskul="props.eskul?.id_eskul"
                            :anggota="props.anggota"
                            @edit="openEditPrestasi" 
                            @add="openTambahPrestasi"
                        />
                    </div>

                    <!-- ROW 5: BOX PENILAIAN (Di Bawah Prestasi) -->
                    <div class="w-full">
                        <BoxPenilaian 
                            :nilai="props.nilai"
                            :anggota="props.anggota"
                            :idEskul="props.eskul?.id_eskul"
                            :currentSemesterInfo="props.currentSemesterInfo"
                        />
                    </div>

                </div>
            </div>
        </main>

        <ModalFormJadwal :show="showModalJadwal" :jadwalData="null" :idEskul="props.eskul?.id_eskul" @close="showModalJadwal = false" />
        <ModalFormAddAnggota :show="showModalAnggota" :idEskul="props.eskul?.id_eskul" :anggotaData="selectedAnggota" @close="showModalAnggota = false" />
        <ModalFormKegiatan :show="showModalKegiatan" :idEskul="props.eskul?.id_eskul" :selectedDate="selectedDate" :kegiatanData="selectedKegiatan" @close="showModalKegiatan = false" />
        
        <!-- Modal Prestasi Baru -->
        <ModalFormPrestasi 
            :show="showModalPrestasi" 
            :idEskul="props.eskul?.id_eskul" 
            :prestasiData="selectedPrestasi" 
            :anggota="props.anggota"
            @close="showModalPrestasi = false" 
        />
    </div>
</template>

<style>
/* Override warna default v-calendar agar sesuai tema */
.vc-blue {
    --vc-accent-50: #f0f9ff;
    --vc-accent-100: #e0f2fe;
    --vc-accent-200: #bae6fd;
    --vc-accent-300: #7dd3fc;
    --vc-accent-400: #38bdf8;
    --vc-accent-500: #0ea5e9;
    --vc-accent-600: #547792;
    --vc-accent-700: #213448;
    --vc-accent-800: #075985;
    --vc-accent-900: #0c4a6e;
}
.vc-container {
    border: none;
    font-family: inherit;
}
.vc-header {
    padding-bottom: 10px;
}
</style>