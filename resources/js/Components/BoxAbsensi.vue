<script setup>
import { computed, ref, watch } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    jadwal: {
        type: Array,
        default: () => []
    },
    anggota: {
        type: Array,
        default: () => []
    },
    selectedDate: {
        type: Date,
        default: () => new Date()
    },
    // Prop baru untuk menerima data existing dari controller
    existingAbsensi: {
        type: Object,
        default: () => ({}) 
    }
});

// Helper: Ubah Date Object ke Nama Hari Indonesia
const getDayName = (date) => {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return days[date.getDay()];
};

const form = useForm({
    tanggal: '',
    absensi: {},
    nilai_harian: {} 
});

const submittedSchedules = ref({});

// State lokal untuk filter tanggal (Default ke props.selectedDate atau Hari Ini)
const page = usePage();
// Cek apakah ada parameter tanggal di URL (agar sinkron saat reload), jika tidak pakai default
const urlParams = new URLSearchParams(window.location.search);
const initialDateStr = urlParams.get('attendance_date') || props.selectedDate.toISOString().split('T')[0];
const filterDate = ref(initialDateStr);

const jadwalHariIni = computed(() => {
    // Gunakan filterDate bukan props.selectedDate untuk reaktivitas lokal
    const checkDate = new Date(filterDate.value);
    
    // Hapus validasi batas 3 hari agar bisa melihat history lampau
    // const today = new Date();
    // today.setHours(0, 0, 0, 0);
    // ... logic diffDays dihapus ...

    const dayName = getDayName(checkDate);
    return props.jadwal
        .filter(j => j.hari === dayName)
        .sort((a, b) => a.jam_mulai.localeCompare(b.jam_mulai));
});

const getAnggotaByJadwal = (jadwal) => {
    return props.anggota.filter(a => {
        const kelasSiswa = Number(a.peserta?.tingkat_kelas);
        // Tampilkan semua siswa (aktif & non-aktif) asalkan kelasnya sesuai
        // Status aktif nanti dicek di template untuk UI overlay
        return kelasSiswa >= jadwal.kelas_min && kelasSiswa <= jadwal.kelas_max;
    });
};

const statusWaktu = computed(() => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const checkDate = new Date(filterDate.value);
    checkDate.setHours(0, 0, 0, 0);

    if (checkDate.getTime() === today.getTime()) return 'today';
    if (checkDate < today) return 'past';
    return 'future';
});

// Watcher Perubahan Tanggal Filter -> Request Data Baru ke Server
watch(filterDate, (newDate) => {
    router.get(
        window.location.pathname, 
        { attendance_date: newDate }, // Kirim parameter tanggal ke controller
        { 
            preserveState: true, // PENTING: Agar tab tidak reset ke awal
            preserveScroll: true,
            replace: true,
            only: ['existingAbsensi', 'kegiatan'] // Request partial data jika didukung backend
        }
    );
});

// Watcher untuk inisialisasi form dengan data existing (agar tidak reset 0 saat refresh)
// Ubah dependency watching ke filterDate
watch([jadwalHariIni, filterDate, () => props.existingAbsensi], ([newJadwalList]) => {
    form.absensi = {};
    form.nilai_harian = {}; 
    form.tanggal = filterDate.value; // Gunakan filterDate
    submittedSchedules.value = {}; // Reset status visual

    newJadwalList.forEach(jadwal => {
        if (!form.absensi[jadwal.id_jadwal]) {
            form.absensi[jadwal.id_jadwal] = {};
            form.nilai_harian[jadwal.id_jadwal] = {}; 
        }

        // Cek data existing untuk jadwal ini
        const existingData = props.existingAbsensi?.[jadwal.id_jadwal];
        
        // Jika data existing ada dan tidak kosong, tandai sebagai "Tersimpan" (Update Mode)
        if (existingData && Object.keys(existingData).length > 0) {
            submittedSchedules.value[jadwal.id_jadwal] = true;
        }

        const anggotaRelevan = getAnggotaByJadwal(jadwal);
        anggotaRelevan.forEach(a => {
            if (a.peserta) {
                const idPeserta = a.peserta.id_peserta;
                
                const dataSiswa = existingData?.[idPeserta];

                if (dataSiswa) {
                    // ISI FORM DENGAN DATA DARI DATABASE
                    form.absensi[jadwal.id_jadwal][idPeserta] = dataSiswa.status;
                    
                    form.nilai_harian[jadwal.id_jadwal][idPeserta] = {
                        teknik: dataSiswa.nilai?.skor_teknik || 0,
                        disiplin: dataSiswa.nilai?.skor_disiplin || 0,
                        kerjasama: dataSiswa.nilai?.skor_kerjasama || 0,
                        catatan_harian: dataSiswa.nilai?.catatan_harian || 'Digenerate Otomatis Oleh Sistem. Mohon isi manual jika diperlukan.'
                    };
                } else {
                    // Default values jika belum ada data
                    form.absensi[jadwal.id_jadwal][idPeserta] = 'Hadir';
                    form.nilai_harian[jadwal.id_jadwal][idPeserta] = {
                        teknik: 0, disiplin: 0, kerjasama: 0, catatan_harian: ''
                    };
                }
            }
        });
    });
}, { immediate: true, deep: true });

const submitAbsensi = (idJadwal) => {
    // 1. Identifikasi ID Peserta yang statusnya AKTIF saja
    const activePesertaIds = props.anggota
        .filter(a => a.status_aktif)
        .map(a => a.peserta?.id_peserta);

    // 2. Filter data absensi & nilai agar hanya menyertakan peserta aktif
    const filteredAbsensi = {};
    const filteredNilai = {};
    
    const rawAbsensi = form.absensi[idJadwal] || {};
    const rawNilai = form.nilai_harian[idJadwal] || {};

    Object.keys(rawAbsensi).forEach(key => {
        const idPeserta = Number(key);
        // Hanya masukkan ke payload jika ID Peserta ada di daftar aktif
        if (activePesertaIds.includes(idPeserta)) {
            filteredAbsensi[key] = rawAbsensi[key];
            if (rawNilai[key]) {
                filteredNilai[key] = rawNilai[key];
            }
        }
    });

    const dataForm = useForm({
        tanggal: form.tanggal,
        id_jadwal: idJadwal,
        data_absensi: filteredAbsensi, // Kirim data yang sudah difilter
        data_nilai: filteredNilai      // Kirim data yang sudah difilter
    });

    dataForm.post('/admin/absensi', {
        preserveScroll: true,
        onSuccess: () => {
            submittedSchedules.value[idJadwal] = true;
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data absensi & nilai berhasil diperbarui (Hanya siswa aktif).',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        },
        onError: () => {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat menyimpan data.' });
        }
    });
};
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col h-full">
        <div class="bg-[#213448] px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <h3 class="text-[#EAE0CF] font-bold text-lg flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Absensi: {{ getDayName(new Date(filterDate)) }}</span>
                </h3>
            </div>
            
            <!-- Filter Tanggal -->
            <div class="flex items-center gap-2 bg-white/10 rounded-lg p-1 border border-[#94B4C1]/20">
                <input 
                    type="date" 
                    v-model="filterDate"
                    class="bg-transparent border-none text-[#EAE0CF] text-sm focus:ring-0 p-1 cursor-pointer font-mono"
                >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#94B4C1] mr-2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <div class="flex-1 p-0 bg-gray-50/50">
            <div v-if="jadwalHariIni.length === 0" class="flex flex-col items-center justify-center h-48 text-center p-6">
                <p class="text-gray-500 font-medium">
                    Tidak ada jadwal latihan pada hari {{ getDayName(new Date(filterDate)) }}.
                </p>
                <p class="text-xs text-gray-400 mt-1">Silakan pilih tanggal lain pada filter di atas.</p>
            </div>

            <div v-else class="p-6 space-y-8">
                <div v-for="(jadwal, index) in jadwalHariIni" :key="jadwal.id_jadwal" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    
                    <div class="px-6 py-3 bg-blue-50 border-b border-blue-100 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded">Sesi {{ index + 1 }}</span>
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">{{ jadwal.jam_mulai }} - {{ jadwal.jam_selesai }}</h4>
                                <p class="text-xs text-gray-500">Kelas {{ jadwal.kelas_min }}-{{ jadwal.kelas_max }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <span v-if="submittedSchedules[jadwal.id_jadwal]" class="text-xs text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded border border-emerald-100 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Data Tersimpan
                            </span>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        <div v-for="item in getAnggotaByJadwal(jadwal)" :key="item.id_anggota" class="relative p-4 hover:bg-gray-50 transition-colors">
                            
                            <!-- Overlay untuk Murid Tidak Aktif -->
                            <div v-if="!item.status_aktif" class="absolute inset-0 z-10 flex items-center justify-center bg-gray-100/60 backdrop-blur-[1px]">
                                <span class="rounded-full bg-gray-200 border border-gray-300 px-3 py-1 text-xs font-bold text-gray-500 shadow-sm">
                                    Murid ini tidak aktif
                                </span>
                            </div>

                            <!-- Konten Absensi (Akan tertutup overlay jika tidak aktif) -->
                            <div :class="{ 'opacity-50 grayscale': !item.status_aktif }">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3 w-1/3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600 border border-gray-200">
                                            {{ item.peserta?.nama_lengkap?.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-800 line-clamp-1">{{ item.peserta?.nama_lengkap }}</p>
                                            <p class="text-[10px] text-gray-500">Kelas {{ item.peserta?.tingkat_kelas }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1" v-if="form.absensi[jadwal.id_jadwal]">
                                        <!-- Fieldset tidak didisable agar bisa diedit -->
                                        <fieldset class="flex gap-1" :disabled="!item.status_aktif">
                                            <label class="cursor-pointer group relative">
                                                <input type="radio" :name="`att-${jadwal.id_jadwal}-${item.id_peserta}`" value="Hadir" v-model="form.absensi[jadwal.id_jadwal][item.peserta.id_peserta]" class="peer sr-only">
                                                <span class="block w-8 h-8 flex items-center justify-center text-[10px] font-bold border border-gray-200 rounded text-gray-400 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:border-emerald-600 hover:bg-emerald-50 transition-all">H</span>
                                                <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-800 text-white text-[10px] py-1 px-2 rounded opacity-90 pointer-events-none">Hadir</span>
                                            </label>
                                            <label class="cursor-pointer group relative">
                                                <input type="radio" :name="`att-${jadwal.id_jadwal}-${item.id_peserta}`" value="Sakit" v-model="form.absensi[jadwal.id_jadwal][item.peserta.id_peserta]" class="peer sr-only">
                                                <span class="block w-8 h-8 flex items-center justify-center text-[10px] font-bold border border-gray-200 rounded text-gray-400 peer-checked:bg-yellow-500 peer-checked:text-white peer-checked:border-yellow-600 hover:bg-yellow-50 transition-all">S</span>
                                                <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-800 text-white text-[10px] py-1 px-2 rounded opacity-90 pointer-events-none">Sakit</span>
                                            </label>
                                            <label class="cursor-pointer group relative">
                                                <input type="radio" :name="`att-${jadwal.id_jadwal}-${item.id_peserta}`" value="Izin" v-model="form.absensi[jadwal.id_jadwal][item.peserta.id_peserta]" class="peer sr-only">
                                                <span class="block w-8 h-8 flex items-center justify-center text-[10px] font-bold border border-gray-200 rounded text-gray-400 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-600 hover:bg-blue-50 transition-all">I</span>
                                                <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-800 text-white text-[10px] py-1 px-2 rounded opacity-90 pointer-events-none">Izin</span>
                                            </label>
                                            <label class="cursor-pointer group relative">
                                                <input type="radio" :name="`att-${jadwal.id_jadwal}-${item.id_peserta}`" value="Alpha" v-model="form.absensi[jadwal.id_jadwal][item.peserta.id_peserta]" class="peer sr-only">
                                                <span class="block w-8 h-8 flex items-center justify-center text-[10px] font-bold border border-gray-200 rounded text-gray-400 peer-checked:bg-red-500 peer-checked:text-white peer-checked:border-red-600 hover:bg-red-50 transition-all">A</span>
                                                <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-800 text-white text-[10px] py-1 px-2 rounded opacity-90 pointer-events-none">Alpha</span>
                                            </label>
                                        </fieldset>
                                    </div>
                                </div>

                                <div v-if="form.absensi[jadwal.id_jadwal] && form.absensi[jadwal.id_jadwal][item.peserta.id_peserta] === 'Hadir'" class="mt-2 pt-2 border-t border-gray-50 flex flex-col gap-2">
                                    <div class="flex items-center gap-4">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider w-16">Nilai Harian:</span>
                                        <div class="flex gap-2 flex-1">
                                            <div class="flex flex-col w-full"><label class="text-[9px] text-gray-500 mb-0.5">Teknik</label><input type="number" min="0" max="100" v-model="form.nilai_harian[jadwal.id_jadwal][item.peserta.id_peserta].teknik" :disabled="!item.status_aktif" class="w-full px-2 py-1 text-xs text-center border border-gray-200 rounded focus:ring-[#547792] focus:border-[#547792] disabled:bg-gray-50" placeholder="0"></div>
                                            <div class="flex flex-col w-full"><label class="text-[9px] text-gray-500 mb-0.5">Disiplin</label><input type="number" min="0" max="100" v-model="form.nilai_harian[jadwal.id_jadwal][item.peserta.id_peserta].disiplin" :disabled="!item.status_aktif" class="w-full px-2 py-1 text-xs text-center border border-gray-200 rounded focus:ring-[#547792] focus:border-[#547792] disabled:bg-gray-50" placeholder="0"></div>
                                            <div class="flex flex-col w-full"><label class="text-[9px] text-gray-500 mb-0.5">Kerjasama</label><input type="number" min="0" max="100" v-model="form.nilai_harian[jadwal.id_jadwal][item.peserta.id_peserta].kerjasama" :disabled="!item.status_aktif" class="w-full px-2 py-1 text-xs text-center border border-gray-200 rounded focus:ring-[#547792] focus:border-[#547792] disabled:bg-gray-50" placeholder="0"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                         <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider w-16">Catatan:</span>
                                         <input type="text" v-model="form.nilai_harian[jadwal.id_jadwal][item.peserta.id_peserta].catatan_harian" :disabled="!item.status_aktif" class="flex-1 px-2 py-1 text-xs border border-gray-200 rounded focus:ring-[#547792] focus:border-[#547792] disabled:bg-gray-50" placeholder="Catatan harian siswa (opsional)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end items-center gap-3">
                        <button 
                            @click="submitAbsensi(jadwal.id_jadwal)"
                            :disabled="statusWaktu === 'future' || getAnggotaByJadwal(jadwal).length === 0"
                            class="flex items-center gap-2 rounded-lg bg-[#213448] px-4 py-2 text-xs font-bold text-[#EAE0CF] shadow-sm hover:bg-[#547792] disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <!-- Teks Tombol Berubah -->
                            <span v-if="submittedSchedules[jadwal.id_jadwal]">Update Absensi & Nilai</span>
                            <span v-else>Simpan Absensi & Nilai</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in-down {
    0% { opacity: 0; transform: translateY(-5px); }
    100% { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fade-in-down 0.2s ease-out;
}
</style>