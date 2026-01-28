<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    nilai: {
        type: Array,
        default: () => []
    },
    anggota: {
        type: Array,
        default: () => []
    },
    idEskul: [Number, String],
    currentSemesterInfo: {
        type: Object,
        default: () => ({ semester: 'Ganjil', tahun: '2025/2026' })
    },
    errors: Object 
});

const selectedTahun = ref(props.currentSemesterInfo.tahun);
const selectedSemester = ref(props.currentSemesterInfo.semester);
const tahunOptions = ['2023/2024', '2024/2025', '2025/2026', '2026/2027'];

const isEditing = ref(false);
const hasData = computed(() => props.nilai && props.nilai.length > 0);

const generateForm = useForm({
    id_eskul: props.idEskul,
    semester: selectedSemester.value,
    tahun_ajaran: selectedTahun.value
});

const updateForm = useForm({ nilai_data: [] });

// Form untuk Sync
const syncForm = useForm({
    id_eskul: props.idEskul,
    semester: selectedSemester.value,
    tahun_ajaran: selectedTahun.value
});

const handleFilterChange = () => {
    console.log(`Mengganti tampilan ke: ${selectedTahun.value} - ${selectedSemester.value}`);
    isEditing.value = false;
    // Update form state agar sync/generate pakai nilai filter terbaru
    generateForm.semester = selectedSemester.value;
    generateForm.tahun_ajaran = selectedTahun.value;
    syncForm.semester = selectedSemester.value;
    syncForm.tahun_ajaran = selectedTahun.value;
};

// Logic Sync / Auto Hitung
const generateFromDaily = () => {
    Swal.fire({
        title: 'Hitung Otomatis?',
        text: "Nilai rapor akan dihitung ulang berdasarkan rata-rata nilai harian semester ini. Data manual akan tertimpa.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hitung',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#213448'
    }).then((result) => {
        if (result.isConfirmed) {
            // Update parameter sebelum kirim
            syncForm.id_eskul = props.idEskul;
            syncForm.semester = selectedSemester.value;
            syncForm.tahun_ajaran = selectedTahun.value;

            syncForm.post('/admin/nilai/sync-daily', {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire('Berhasil', 'Nilai berhasil disinkronisasi dari data harian.', 'success');
                    isEditing.value = false; // Keluar mode edit agar data refresh
                },
                onError: () => Swal.fire('Gagal', 'Terjadi kesalahan saat sinkronisasi.', 'error')
            });
        }
    });
};

const initializePenilaian = () => {
    generateForm.id_eskul = props.idEskul;
    generateForm.semester = selectedSemester.value; 
    generateForm.tahun_ajaran = selectedTahun.value;

    Swal.fire({
        title: 'Buka Penilaian?',
        text: `Mulai penilaian untuk ${selectedSemester.value} ${selectedTahun.value}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Buka'
    }).then((result) => {
        if (result.isConfirmed) {
            generateForm.post('/admin/nilai/generate', {
                preserveScroll: true,
                onSuccess: () => Swal.fire('Berhasil', 'Periode penilaian dibuka.', 'success'),
                onError: (errors) => {
                    let msg = errors.message || 'Gagal membuka periode penilaian. Pastikan ada anggota aktif.';
                    Swal.fire('Gagal', msg, 'error');
                }
            });
        }
    });
};

const toggleEdit = () => {
    if (!isEditing.value) {
        updateForm.nilai_data = props.nilai.map(n => ({
            id_nilai: n.id_nilai,
            nilai_disiplin: n.nilai_disiplin,
            nilai_teknik: n.nilai_teknik,
            nilai_kerjasama: n.nilai_kerjasama,
            catatan_rapor: n.catatan_rapor
        }));
    } else {
        updateForm.reset();
    }
    isEditing.value = !isEditing.value;
};

const saveNilai = () => {
    updateForm.put('/admin/nilai/update-bulk', {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
            Swal.fire('Tersimpan', 'Data nilai berhasil diperbarui.', 'success');
        },
        onError: () => Swal.fire('Gagal', 'Terjadi kesalahan validasi.', 'error')
    });
};

const downloadExcel = () => {
    const params = new URLSearchParams({
        id_eskul: props.idEskul,
        semester: selectedSemester.value,
        tahun_ajaran: selectedTahun.value
    }).toString();
    window.open(`/admin/nilai/export?${params}`, '_blank');
};

const getPredikat = (n1, n2, n3) => {
    const avg = (Number(n1) + Number(n2) + Number(n3)) / 3;
    if (avg >= 90) return 'A';
    if (avg >= 80) return 'B';
    if (avg >= 70) return 'C';
    return 'D';
};
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden w-full relative">
        
        <!-- Header dengan Filter "Time Travel" -->
        <div class="bg-[#213448] px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
            
            <!-- Kiri: Judul -->
            <div class="flex items-center gap-2 text-[#EAE0CF]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="font-bold text-lg whitespace-nowrap">Penilaian & Rapor</h3>
            </div>
            
            <!-- Kanan: Filter & Aksi -->
            <div class="flex flex-wrap items-center justify-end gap-2 w-full md:w-auto">
                
                <!-- Dropdown Tahun -->
                <select 
                    v-model="selectedTahun" 
                    @change="handleFilterChange"
                    class="py-1.5 px-3 text-xs rounded-lg border-none bg-white/10 text-white focus:ring-2 focus:ring-[#547792] cursor-pointer"
                >
                    <option v-for="t in tahunOptions" :key="t" :value="t" class="text-gray-800">{{ t }}</option>
                </select>

                <!-- Dropdown Semester -->
                <select 
                    v-model="selectedSemester" 
                    @change="handleFilterChange"
                    class="py-1.5 px-3 text-xs rounded-lg border-none bg-white/10 text-white focus:ring-2 focus:ring-[#547792] cursor-pointer"
                >
                    <option value="Ganjil" class="text-gray-800">Ganjil</option>
                    <option value="Genap" class="text-gray-800">Genap</option>
                </select>

                <div class="w-px h-6 bg-white/20 mx-1"></div> <!-- Separator -->

                <!-- Tombol Aksi (Hanya jika ada data) -->
                <div v-if="hasData" class="flex gap-2">
                    
                    <!-- Tombol Download Excel -->
                    <button 
                        @click="downloadExcel"
                        class="p-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-500 transition shadow-sm"
                        title="Download Excel"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    </button>

                    <template v-if="!isEditing">
                        <button @click="toggleEdit" class="text-xs bg-white/10 text-white px-3 py-1.5 rounded hover:bg-white hover:text-[#213448] transition flex items-center gap-1 font-bold shadow-sm border border-transparent">
                            Edit Nilai
                        </button>
                    </template>
                    
                    <template v-else>
                        <!-- Tombol "Hitung dari Harian" (Aktif) -->
                        <button 
                            @click="generateFromDaily"
                            :disabled="syncForm.processing"
                            class="text-xs bg-blue-500/20 text-blue-200 border border-blue-500/50 px-3 py-1.5 rounded hover:bg-blue-600 hover:text-white transition flex items-center gap-1"
                            title="Ambil rata-rata dari nilai harian"
                        >
                            <svg v-if="!syncForm.processing" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            <span v-else>Syncing...</span>
                            <span v-if="!syncForm.processing">Auto Hitung</span>
                        </button>

                        <button @click="toggleEdit" class="text-xs bg-red-500/20 text-red-200 px-3 py-1.5 rounded hover:bg-red-500 hover:text-white transition">Batal</button>
                        <button @click="saveNilai" :disabled="updateForm.processing" class="text-xs bg-emerald-500 text-white px-4 py-1.5 rounded hover:bg-emerald-600 transition shadow-sm font-bold">
                            {{ updateForm.processing ? '...' : 'Simpan' }}
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- STATE 1: LOCKED / BELUM ADA DATA -->
        <div v-if="!hasData" class="p-10 flex flex-col items-center justify-center text-center bg-gray-50/50">
            <div class="bg-gray-100 p-4 rounded-full mb-4 shadow-inner border border-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h4 class="text-lg font-bold text-gray-700 mb-2">
                Penilaian {{ selectedSemester }} {{ selectedTahun }} Terkunci
            </h4>
            <p class="text-gray-500 text-sm max-w-md mb-6">
                Data penilaian untuk periode yang Anda pilih belum tersedia. Pastikan ada anggota aktif pada tahun ajaran ini sebelum membuka penilaian.
            </p>
            
            <div v-if="$page.props.errors?.message" class="mb-4 text-red-500 text-sm font-bold bg-red-50 px-4 py-2 rounded">
                {{ $page.props.errors.message }}
            </div>

            <button @click="initializePenilaian" :disabled="generateForm.processing" class="px-6 py-2.5 rounded-lg bg-[#213448] text-[#EAE0CF] font-bold text-sm shadow-md hover:bg-[#547792] transition flex items-center gap-2 transform active:scale-95">
                <span v-if="generateForm.processing">Memproses...</span>
                <span v-else>Buka Periode Penilaian</span>
            </button>
        </div>

        <!-- STATE 2: OPEN / TABEL NILAI -->
        <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-100">Anggota</th>
                        <th class="px-4 py-3 border-b border-gray-100 text-center w-24">% Hadir</th>
                        <th class="px-4 py-3 border-b border-gray-100 text-center w-24">Disiplin</th>
                        <th class="px-4 py-3 border-b border-gray-100 text-center w-24">Teknik</th>
                        <th class="px-4 py-3 border-b border-gray-100 text-center w-24">Kerjasama</th>
                        <th class="px-4 py-3 border-b border-gray-100 text-center w-20">Predikat</th>
                        <th class="px-6 py-3 border-b border-gray-100 w-64">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="(item, index) in nilai" :key="item.id_nilai" class="hover:bg-gray-50 transition-colors group">
                        
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#547792]/10 text-[#547792] flex items-center justify-center text-xs font-bold shrink-0 border border-[#547792]/20">
                                    {{ item.anggota_eskul?.peserta?.nama_lengkap?.charAt(0) || '?' }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">{{ item.anggota_eskul?.peserta?.nama_lengkap || 'Unknown' }}</p>
                                    <p class="text-[10px] text-gray-500">Kls {{ item.anggota_eskul?.peserta?.tingkat_kelas || '-' }}</p>
                                </div>
                            </div>
                        </td>
                        
                        <!-- % Hadir -->
                        <td class="px-4 py-3 text-center">
                            <span 
                                class="inline-flex items-center justify-center px-2 py-1 rounded text-xs font-bold border"
                                :class="item.persentase_hadir >= 75 ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100'"
                                :title="`${item.statistik_hadir} dari ${item.total_pertemuan} pertemuan`"
                            >
                                {{ item.persentase_hadir }}%
                            </span>
                        </td>

                        <!-- Input Fields -->
                        <td class="px-4 py-3 text-center">
                            <input v-if="isEditing" type="number" min="0" max="100" v-model="updateForm.nilai_data[index].nilai_disiplin" class="w-16 px-1 py-1 text-center text-sm border border-gray-300 rounded focus:ring-[#547792] focus:border-[#547792] shadow-sm">
                            <span v-else class="text-sm font-medium text-gray-700">{{ item.nilai_disiplin }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input v-if="isEditing" type="number" min="0" max="100" v-model="updateForm.nilai_data[index].nilai_teknik" class="w-16 px-1 py-1 text-center text-sm border border-gray-300 rounded focus:ring-[#547792] focus:border-[#547792] shadow-sm">
                            <span v-else class="text-sm font-medium text-gray-700">{{ item.nilai_teknik }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input v-if="isEditing" type="number" min="0" max="100" v-model="updateForm.nilai_data[index].nilai_kerjasama" class="w-16 px-1 py-1 text-center text-sm border border-gray-300 rounded focus:ring-[#547792] focus:border-[#547792] shadow-sm">
                            <span v-else class="text-sm font-medium text-gray-700">{{ item.nilai_kerjasama }}</span>
                        </td>

                        <!-- Predikat -->
                        <td class="px-4 py-3 text-center">
                            <span 
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold border transition-colors duration-300"
                                :class="{
                                    'bg-emerald-100 text-emerald-700 border-emerald-200': (isEditing ? getPredikat(updateForm.nilai_data[index].nilai_disiplin, updateForm.nilai_data[index].nilai_teknik, updateForm.nilai_data[index].nilai_kerjasama) : getPredikat(item.nilai_disiplin, item.nilai_teknik, item.nilai_kerjasama)) === 'A',
                                    'bg-blue-100 text-blue-700 border-blue-200': (isEditing ? getPredikat(updateForm.nilai_data[index].nilai_disiplin, updateForm.nilai_data[index].nilai_teknik, updateForm.nilai_data[index].nilai_kerjasama) : getPredikat(item.nilai_disiplin, item.nilai_teknik, item.nilai_kerjasama)) === 'B',
                                    'bg-yellow-100 text-yellow-700 border-yellow-200': (isEditing ? getPredikat(updateForm.nilai_data[index].nilai_disiplin, updateForm.nilai_data[index].nilai_teknik, updateForm.nilai_data[index].nilai_kerjasama) : getPredikat(item.nilai_disiplin, item.nilai_teknik, item.nilai_kerjasama)) === 'C',
                                    'bg-red-100 text-red-700 border-red-200': (isEditing ? getPredikat(updateForm.nilai_data[index].nilai_disiplin, updateForm.nilai_data[index].nilai_teknik, updateForm.nilai_data[index].nilai_kerjasama) : getPredikat(item.nilai_disiplin, item.nilai_teknik, item.nilai_kerjasama)) === 'D',
                                }"
                            >
                                {{ isEditing ? getPredikat(updateForm.nilai_data[index].nilai_disiplin, updateForm.nilai_data[index].nilai_teknik, updateForm.nilai_data[index].nilai_kerjasama) : getPredikat(item.nilai_disiplin, item.nilai_teknik, item.nilai_kerjasama) }}
                            </span>
                        </td>

                        <!-- Catatan -->
                        <td class="px-6 py-3">
                            <textarea v-if="isEditing" v-model="updateForm.nilai_data[index].catatan_rapor" rows="1" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-[#547792] focus:border-[#547792] shadow-sm resize-none" placeholder="Catatan..."></textarea>
                            <p v-else class="text-xs text-gray-500 italic truncate max-w-[200px]">{{ item.catatan_rapor || '-' }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>