<script setup>
import { ref, watch, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2'; // Import SweetAlert2

const props = defineProps({
    nilai: {
        type: Array,
        default: () => []
    },
    idEskul: {
        type: [Number, String],
        required: true
    },
    // Semester REAL-TIME hari ini (default sistem)
    currentSemesterInfo: {
        type: Object,
        default: () => ({ semester: 'Ganjil', tahun: '2025/2026' })
    },
    // Filter yang SEDANG DIPILIH (dari Controller)
    activeNilaiFilter: {
        type: Object,
        default: () => null 
    }
});

// Gunakan filter aktif dari server jika ada, jika tidak gunakan default hari ini
const activeFilter = computed(() => props.activeNilaiFilter || props.currentSemesterInfo);

// State Lokal Filter (Dropdown)
const selectedSemester = ref(activeFilter.value.semester);
const selectedTahun = ref(activeFilter.value.tahun || activeFilter.value.tahun_ajaran);

// State Mode Edit
const isEditing = ref(false);
const processing = ref(false);

// Generate opsi Tahun Ajaran (Current +/- 2 tahun)
const yearOptions = computed(() => {
    // Ambil tahun dari currentSemesterInfo sebagai pivot agar dinamis
    const currentYearStr = props.currentSemesterInfo.tahun; // Misal "2025/2026"
    const startYear = parseInt(currentYearStr.split('/')[0]); // 2025
    
    let years = [];
    // Tampilkan 2 tahun ke belakang dan 1 tahun ke depan
    for (let i = -2; i <= 1; i++) {
        const y = startYear + i;
        years.push(`${y}/${y + 1}`);
    }
    return years;
});

// Watcher: Reload halaman saat filter berubah
watch([selectedSemester, selectedTahun], ([newSem, newTahun]) => {
    // PERBAIKAN: Gunakan URL API native JS untuk update params tanpa dependency 'route'
    const url = new URL(window.location.href);
    url.searchParams.set('semester', newSem);
    url.searchParams.set('tahun_ajaran', newTahun);

    router.get(
        url.toString(),
        {},
        { 
            preserveState: true, 
            preserveScroll: true, 
            only: ['nilai', 'activeNilaiFilter'] // Partial reload agar ringan
        }
    );
});

// Form Generate (Buka Periode)
const formGenerate = useForm({
    id_eskul: props.idEskul,
    semester: '', 
    tahun_ajaran: '', 
});

const generateNilai = () => {
    // Pastikan mengirim data sesuai filter yang sedang dilihat
    formGenerate.semester = selectedSemester.value;
    formGenerate.tahun_ajaran = selectedTahun.value;

    Swal.fire({
        title: 'Buka Periode Penilaian?',
        text: `Anda akan membuka penilaian untuk Semester ${selectedSemester.value} ${selectedTahun.value}.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#213448', // Warna tema aplikasi
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Buka!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            formGenerate.post('/admin/nilai/generate', {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Periode penilaian berhasil dibuka.',
                        icon: 'success',
                        confirmButtonColor: '#213448'
                    });
                }
            });
        }
    });
};

// Form Sync Harian
const formSync = useForm({
    id_eskul: props.idEskul,
    semester: '',
    tahun_ajaran: '',
});

const syncFromDaily = () => {
    formSync.semester = selectedSemester.value;
    formSync.tahun_ajaran = selectedTahun.value;

    Swal.fire({
        title: 'Sinkronisasi Nilai Harian?',
        text: `Nilai manual akan tertimpa dengan rata-rata harian untuk Semester ${selectedSemester.value} ${selectedTahun.value}. Lanjutkan?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#213448',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Sinkronkan!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            formSync.post('/admin/nilai/sync-daily', {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Nilai berhasil ditarik dari data harian.',
                        icon: 'success',
                        confirmButtonColor: '#213448'
                    });
                }
            });
        }
    });
};

// Form Bulk Update
const formUpdate = useForm({
    nilai_data: []
});

const saveChanges = () => {
    processing.value = true;
    formUpdate.nilai_data = props.nilai.map(item => ({
        id_nilai: item.id_nilai,
        nilai_teknik: item.nilai_teknik,
        nilai_disiplin: item.nilai_disiplin,
        nilai_kerjasama: item.nilai_kerjasama,
        catatan_rapor: item.catatan_rapor
    }));

    formUpdate.put('/admin/nilai/update-bulk', {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
            processing.value = false;
            Swal.fire({
                title: 'Tersimpan!',
                text: 'Semua perubahan berhasil disimpan.',
                icon: 'success',
                confirmButtonColor: '#213448'
            });
        },
        onError: () => {
            processing.value = false;
            Swal.fire({
                title: 'Gagal!',
                text: 'Gagal menyimpan data. Silakan cek kembali inputan Anda.',
                icon: 'error',
                confirmButtonColor: '#213448'
            });
        }
    });
};

const exportExcel = () => {
    const params = new URLSearchParams({
        id_eskul: props.idEskul,
        semester: selectedSemester.value,
        tahun_ajaran: selectedTahun.value,
    }).toString();
    
    window.open(`/admin/nilai/export?${params}`, '_blank');
};

// Helper Display
const getFinalScore = (t, d, k) => Math.round((Number(t) + Number(d) + Number(k)) / 3);
const getPredikat = (score) => {
    if (score >= 90) return 'A';
    if (score >= 80) return 'B';
    if (score >= 70) return 'C';
    return 'D';
};
</script>

<template>
    <div class="space-y-6">
        
        <!-- HEADER TOOLBAR -->
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
            
            <!-- Kiri: Judul & Filter -->
            <div class="flex flex-col md:flex-row md:items-center gap-4 w-full xl:w-auto">
                <div>
                    <h3 class="font-bold text-lg text-[#213448] flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Input Nilai Rapor
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">Kelola nilai akhir semester siswa</p>
                </div>

                <!-- Divider -->
                <div class="hidden md:block h-8 w-px bg-gray-200 mx-2"></div>

                <!-- Filter Controls -->
                <div class="flex items-center gap-2 bg-gray-50 p-1.5 rounded-lg border border-gray-200 shadow-sm">
                    <!-- Dropdown Tahun -->
                    <select 
                        v-model="selectedTahun" 
                        class="text-sm border-none bg-transparent focus:ring-0 text-gray-700 font-bold py-1 pl-2 pr-8 cursor-pointer hover:bg-white hover:shadow-sm rounded transition"
                        title="Pilih Tahun Ajaran"
                    >
                        <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
                    </select>
                    
                    <div class="h-4 w-px bg-gray-300"></div>

                    <!-- Dropdown Semester -->
                    <select 
                        v-model="selectedSemester" 
                        class="text-sm border-none bg-transparent focus:ring-0 text-gray-700 font-bold py-1 pl-2 pr-8 cursor-pointer hover:bg-white hover:shadow-sm rounded transition"
                        title="Pilih Semester"
                    >
                        <option value="Ganjil">Sem. Ganjil</option>
                        <option value="Genap">Sem. Genap</option>
                    </select>
                </div>
            </div>

            <!-- Kanan: Action Buttons -->
            <div class="flex flex-wrap items-center gap-2 w-full xl:w-auto justify-end">
                
                <!-- KONDISI 1: Jika Data KOSONG -->
                <button 
                    v-if="nilai.length === 0"
                    @click="generateNilai"
                    class="px-4 py-2 bg-[#213448] text-[#EAE0CF] rounded-lg text-sm font-bold hover:bg-[#1a2a3a] transition flex items-center gap-2 shadow-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Buka Periode Ini
                </button>

                <!-- KONDISI 2: Jika Data ADA -->
                <template v-else>
                    <button 
                        @click="syncFromDaily"
                        class="px-3 py-2 bg-white text-blue-600 border border-blue-200 rounded-lg text-sm font-medium hover:bg-blue-50 transition flex items-center gap-2 shadow-sm"
                        title="Tarik nilai dari rata-rata harian"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="hidden md:inline">Sinkron Harian</span>
                    </button>

                    <button 
                        @click="exportExcel"
                        class="px-3 py-2 bg-white text-emerald-600 border border-emerald-200 rounded-lg text-sm font-medium hover:bg-emerald-50 transition flex items-center gap-2 shadow-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Excel
                    </button>

                    <div class="hidden sm:block h-6 w-px bg-gray-300 mx-2"></div>

                    <button 
                        v-if="!isEditing"
                        @click="isEditing = true"
                        class="px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm font-bold hover:bg-yellow-600 transition shadow-sm flex items-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Mode Edit
                    </button>

                    <div v-else class="flex gap-2">
                        <button 
                            @click="isEditing = false"
                            class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-300 transition"
                        >
                            Batal
                        </button>
                        <button 
                            @click="saveChanges"
                            :disabled="processing"
                            class="px-4 py-2 bg-[#213448] text-[#EAE0CF] rounded-lg text-sm font-bold hover:bg-[#1a2a3a] transition shadow-sm flex items-center gap-2"
                        >
                            <svg v-if="processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Simpan
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- CONTENT AREA -->
        
        <!-- STATE 1: Data Belum Ada (Empty State) -->
        <div v-if="nilai.length === 0" class="bg-white p-12 rounded-xl border border-dashed border-gray-300 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            
            <!-- Judul Empty State -->
            <h3 class="text-lg font-bold text-gray-700">Belum Ada Data Nilai</h3>
            
            <!-- Deskripsi Dinamis sesuai Filter yang Dipilih -->
            <p class="text-gray-500 max-w-lg mx-auto mt-2 mb-6">
                Tidak ditemukan data nilai untuk <span class="font-bold text-[#213448]">Semester {{ selectedSemester }}</span> 
                Tahun Ajaran <span class="font-bold text-[#213448]">{{ selectedTahun }}</span>.
                <br><span class="text-xs">Silakan pilih semester lain di pojok kanan atas atau buka periode baru.</span>
            </p>

            <button 
                @click="generateNilai"
                class="px-6 py-2.5 bg-[#213448] text-[#EAE0CF] rounded-lg font-bold hover:bg-[#1a2a3a] transition shadow-lg"
            >
                Buka Periode Ini
            </button>
        </div>

        <!-- STATE 2: Tabel Penilaian -->
        <div v-else class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4 border-b border-gray-100 w-10">No</th>
                            <th class="px-6 py-4 border-b border-gray-100">Nama Siswa</th>
                            <th class="px-6 py-4 border-b border-gray-100 text-center w-24">Statistik</th>
                            <th class="px-2 py-4 border-b border-gray-100 text-center w-20">Teknik</th>
                            <th class="px-2 py-4 border-b border-gray-100 text-center w-20">Disiplin</th>
                            <th class="px-2 py-4 border-b border-gray-100 text-center w-20">Kerjasama</th>
                            <th class="px-2 py-4 border-b border-gray-100 text-center w-20 bg-gray-100">Akhir</th>
                            <th class="px-6 py-4 border-b border-gray-100">Catatan Rapor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(item, index) in nilai" :key="item.id_nilai" class="hover:bg-gray-50 transition-colors group">
                            <td class="px-6 py-4 text-center text-sm text-gray-400">{{ index + 1 }}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-800">{{ item.anggota_eskul?.peserta?.nama_lengkap }}</p>
                                <p class="text-xs text-gray-500">{{ item.anggota_eskul?.peserta?.tingkat_kelas }} - {{ item.anggota_eskul?.peserta?.jenis_kelamin }}</p>
                            </td>
                            
                            <!-- Statistik Kehadiran (Read Only) -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-xs font-bold text-[#547792]">
                                        {{ item.persentase_hadir }}%
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        {{ item.statistik_hadir }}/{{ item.total_pertemuan }} H
                                    </span>
                                </div>
                            </td>

                            <!-- Input Nilai -->
                            <td class="px-2 py-4 text-center">
                                <input 
                                    v-if="isEditing" 
                                    v-model.number="item.nilai_teknik" 
                                    type="number" min="0" max="100" 
                                    class="w-16 text-center text-sm border-gray-300 rounded focus:ring-[#547792] focus:border-[#547792]"
                                >
                                <span v-else class="text-sm font-medium text-gray-700">{{ item.nilai_teknik }}</span>
                            </td>
                            <td class="px-2 py-4 text-center">
                                <input 
                                    v-if="isEditing" 
                                    v-model.number="item.nilai_disiplin" 
                                    type="number" min="0" max="100" 
                                    class="w-16 text-center text-sm border-gray-300 rounded focus:ring-[#547792] focus:border-[#547792]"
                                >
                                <span v-else class="text-sm font-medium text-gray-700">{{ item.nilai_disiplin }}</span>
                            </td>
                            <td class="px-2 py-4 text-center">
                                <input 
                                    v-if="isEditing" 
                                    v-model.number="item.nilai_kerjasama" 
                                    type="number" min="0" max="100" 
                                    class="w-16 text-center text-sm border-gray-300 rounded focus:ring-[#547792] focus:border-[#547792]"
                                >
                                <span v-else class="text-sm font-medium text-gray-700">{{ item.nilai_kerjasama }}</span>
                            </td>

                            <!-- Nilai Akhir (Auto Calculated) -->
                            <td class="px-2 py-4 text-center bg-gray-50">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-bold text-[#213448]">
                                        {{ getFinalScore(item.nilai_teknik, item.nilai_disiplin, item.nilai_kerjasama) }}
                                    </span>
                                    <span class="text-[10px] font-bold px-1.5 rounded bg-gray-200 text-gray-600">
                                        {{ getPredikat(getFinalScore(item.nilai_teknik, item.nilai_disiplin, item.nilai_kerjasama)) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Catatan -->
                            <td class="px-6 py-4">
                                <textarea 
                                    v-if="isEditing" 
                                    v-model="item.catatan_rapor" 
                                    rows="2"
                                    class="w-full text-xs border-gray-300 rounded focus:ring-[#547792] focus:border-[#547792] resize-none"
                                    placeholder="Catatan perkembangan siswa..."
                                ></textarea>
                                <p v-else class="text-xs text-gray-600 italic line-clamp-2" :title="item.catatan_rapor">
                                    {{ item.catatan_rapor || '-' }}
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Footer Info -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 text-xs text-gray-500 flex justify-between">
                <span>Total Siswa Dinilai: <b>{{ nilai.length }}</b></span>
                <span class="italic text-gray-400">Filter: {{ selectedSemester }} {{ selectedTahun }}</span>
            </div>
        </div>
    </div>
</template>