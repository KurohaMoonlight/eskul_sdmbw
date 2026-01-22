<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
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
    errors: Object // Terima props errors dari Inertia
});

// State Editing
const isEditing = ref(false);

const hasData = computed(() => props.nilai && props.nilai.length > 0);

// Form Generate
const generateForm = useForm({
    id_eskul: props.idEskul,
    semester: props.currentSemesterInfo.semester,
    tahun_ajaran: props.currentSemesterInfo.tahun
});

// Form Update
const updateForm = useForm({
    nilai_data: []
});

const initializePenilaian = () => {
    // Pastikan ID Eskul terisi sebelum kirim
    generateForm.id_eskul = props.idEskul;
    generateForm.semester = props.currentSemesterInfo.semester;
    generateForm.tahun_ajaran = props.currentSemesterInfo.tahun;

    Swal.fire({
        title: 'Buka Penilaian?',
        text: `Mulai penilaian untuk ${props.currentSemesterInfo.semester} ${props.currentSemesterInfo.tahun}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Buka'
    }).then((result) => {
        if (result.isConfirmed) {
            generateForm.post('/admin/nilai/generate', {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire('Berhasil', 'Periode penilaian dibuka.', 'success');
                    // Paksa reload props jika perlu, meski biasanya back() sudah cukup
                    // router.reload({ only: ['nilai'] }); 
                },
                onError: (errors) => {
                    // Tampilkan error jika gagal (misal: tidak ada anggota aktif)
                    // Pesan error dari controller ada di errors.message (jika pakai withErrors)
                    // atau di flash message.
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

const getPredikat = (n1, n2, n3) => {
    const avg = (n1 + n2 + n3) / 3;
    if (avg >= 90) return 'A';
    if (avg >= 80) return 'B';
    if (avg >= 70) return 'C';
    return 'D';
};
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden w-full relative">
        <!-- Header -->
        <div class="bg-[#213448] px-6 py-4 flex items-center justify-between">
            <h3 class="text-[#EAE0CF] font-bold text-lg flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Penilaian Anggota
            </h3>
            
            <div class="flex items-center gap-2">
                <span class="text-[#94B4C1] text-xs font-mono bg-[#94B4C1]/10 px-2 py-1 rounded">
                    {{ currentSemesterInfo.semester }} {{ currentSemesterInfo.tahun }}
                </span>
                <div v-if="hasData">
                    <button v-if="!isEditing" @click="toggleEdit" class="text-xs bg-white/10 text-white px-3 py-1.5 rounded hover:bg-white hover:text-[#213448] transition flex items-center gap-1 font-bold shadow-sm">
                        Edit Nilai
                    </button>
                    <div v-else class="flex gap-2">
                        <button @click="toggleEdit" class="text-xs bg-red-500/20 text-red-200 px-3 py-1.5 rounded hover:bg-red-500 hover:text-white transition">Batal</button>
                        <button @click="saveNilai" :disabled="updateForm.processing" class="text-xs bg-emerald-500 text-white px-4 py-1.5 rounded hover:bg-emerald-600 transition shadow-sm font-bold">
                            {{ updateForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- STATE 1: LOCKED -->
        <div v-if="!hasData" class="p-10 flex flex-col items-center justify-center text-center bg-gray-50/50">
            <div class="bg-gray-100 p-4 rounded-full mb-4 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h4 class="text-lg font-bold text-gray-700 mb-2">Penilaian Belum Dibuka</h4>
            <p class="text-gray-500 text-sm max-w-md mb-6">
                Data penilaian semester ini belum tersedia. Pastikan ada anggota aktif di tahun ajaran <strong>{{ currentSemesterInfo.tahun }}</strong>.
            </p>
            
            <!-- Tampilkan Error Message dari Backend jika ada -->
            <div v-if="$page.props.errors?.message" class="mb-4 text-red-500 text-sm font-bold bg-red-50 px-4 py-2 rounded">
                {{ $page.props.errors.message }}
            </div>

            <button @click="initializePenilaian" :disabled="generateForm.processing" class="px-6 py-2.5 rounded-lg bg-[#213448] text-[#EAE0CF] font-bold text-sm shadow-md hover:bg-[#547792] transition flex items-center gap-2">
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
                                <div class="w-8 h-8 rounded-full bg-[#547792]/10 text-[#547792] flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ item.anggota_eskul?.peserta?.nama_lengkap?.charAt(0) || '?' }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">{{ item.anggota_eskul?.peserta?.nama_lengkap || 'Unknown' }}</p>
                                    <p class="text-[10px] text-gray-500">Kls {{ item.anggota_eskul?.peserta?.tingkat_kelas || '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input v-if="isEditing" type="number" min="0" max="100" v-model="updateForm.nilai_data[index].nilai_disiplin" class="w-16 px-1 py-1 text-center text-sm border border-gray-300 rounded focus:ring-[#547792]">
                            <span v-else class="text-sm font-medium text-gray-700">{{ item.nilai_disiplin }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input v-if="isEditing" type="number" min="0" max="100" v-model="updateForm.nilai_data[index].nilai_teknik" class="w-16 px-1 py-1 text-center text-sm border border-gray-300 rounded focus:ring-[#547792]">
                            <span v-else class="text-sm font-medium text-gray-700">{{ item.nilai_teknik }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input v-if="isEditing" type="number" min="0" max="100" v-model="updateForm.nilai_data[index].nilai_kerjasama" class="w-16 px-1 py-1 text-center text-sm border border-gray-300 rounded focus:ring-[#547792]">
                            <span v-else class="text-sm font-medium text-gray-700">{{ item.nilai_kerjasama }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span 
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold border"
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
                        <td class="px-6 py-3">
                            <textarea v-if="isEditing" v-model="updateForm.nilai_data[index].catatan_rapor" rows="1" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-[#547792] resize-none"></textarea>
                            <p v-else class="text-xs text-gray-500 italic truncate max-w-[200px]">{{ item.catatan_rapor || '-' }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>