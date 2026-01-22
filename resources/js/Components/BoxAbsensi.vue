<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
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
    }
});

// Helper: Ubah Date Object ke Nama Hari Indonesia
const getDayName = (date) => {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return days[date.getDay()];
};

// State: Menyimpan input absensi sementara
// Struktur: { [id_jadwal]: { [id_peserta]: 'Hadir' } }
const form = useForm({
    tanggal: '',
    absensi: {} 
});

// State lokal untuk menandai jadwal yang sudah disubmit (agar tombol disable)
const submittedSchedules = ref({});

// 1. Filter Jadwal Hari Ini
const jadwalHariIni = computed(() => {
    const dayName = getDayName(props.selectedDate);
    
    return props.jadwal
        .filter(j => j.hari === dayName)
        // Urutkan berdasarkan jam mulai
        .sort((a, b) => a.jam_mulai.localeCompare(b.jam_mulai));
});

// 2. Filter Anggota Sesuai Kelas Jadwal
const getAnggotaByJadwal = (jadwal) => {
    return props.anggota.filter(a => {
        const kelasSiswa = Number(a.peserta?.tingkat_kelas);
        // Pastikan status aktif dan kelas masuk rentang jadwal
        return a.status_aktif && kelasSiswa >= jadwal.kelas_min && kelasSiswa <= jadwal.kelas_max;
    });
};

// 3. Status Waktu (Today, Past, Future)
const statusWaktu = computed(() => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const check = new Date(props.selectedDate);
    check.setHours(0, 0, 0, 0);

    if (check.getTime() === today.getTime()) return 'today';
    if (check < today) return 'past';
    return 'future';
});

// Watcher untuk inisialisasi form saat tanggal/jadwal berubah
watch([jadwalHariIni, () => props.selectedDate], ([newJadwalList]) => {
    form.absensi = {};
    form.tanggal = props.selectedDate.toISOString().split('T')[0];
    submittedSchedules.value = {}; // Reset status submit visual

    newJadwalList.forEach(jadwal => {
        // Init object untuk jadwal ini
        if (!form.absensi[jadwal.id_jadwal]) {
            form.absensi[jadwal.id_jadwal] = {};
        }

        // Default 'Hadir' untuk semua siswa yang relevan
        const anggota = getAnggotaByJadwal(jadwal);
        anggota.forEach(a => {
            if (a.peserta) {
                form.absensi[jadwal.id_jadwal][a.peserta.id_peserta] = 'Hadir';
            }
        });
    });
}, { immediate: true });

// Submit
const submitAbsensi = (idJadwal) => {
    // Client-side guard
    if (submittedSchedules.value[idJadwal]) return;

    // Gunakan helper form inertia manual agar tidak mengirim semua state form global
    const singleSubmitForm = useForm({
        tanggal: form.tanggal,
        id_jadwal: idJadwal,
        data_absensi: form.absensi[idJadwal]
    });

    singleSubmitForm.post('/admin/absensi', {
        preserveScroll: true,
        onSuccess: () => {
            submittedSchedules.value[idJadwal] = true;
            Swal.fire({
                icon: 'success',
                title: 'Tersimpan',
                text: 'Data absensi berhasil disimpan.',
                timer: 2000,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        },
        onError: () => {
            Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan.', 'error');
        }
    });
};
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex flex-col h-full w-full">
        <!-- Header -->
        <div class="bg-[#213448] px-6 py-4 flex items-center justify-between">
            <h3 class="text-[#EAE0CF] font-bold text-lg flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Absensi: {{ getDayName(selectedDate) }}
            </h3>
            <span class="text-[#94B4C1] text-xs font-mono bg-[#94B4C1]/10 px-2 py-1 rounded">
                {{ selectedDate.toLocaleDateString('id-ID') }}
            </span>
        </div>

        <div class="flex-1 bg-gray-50/50 p-6">
            <!-- EMPTY STATE -->
            <div v-if="jadwalHariIni.length === 0" class="flex flex-col items-center justify-center h-40 text-center">
                <div class="bg-white p-3 rounded-full mb-3 shadow-sm">
                    <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-gray-500 font-medium text-sm">Tidak ada jadwal latihan pada hari ini.</p>
            </div>

            <!-- JADWAL LIST -->
            <div v-else class="space-y-8">
                <!-- Loop Jadwal (Handle multiple schedules) -->
                <div v-for="(jadwal, idx) in jadwalHariIni" :key="jadwal.id_jadwal" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    
                    <!-- Header Per Jadwal -->
                    <div class="px-6 py-3 bg-blue-50 border-b border-blue-100 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded-md">
                                Sesi {{ idx + 1 }}
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">
                                    {{ jadwal.jam_mulai }} - {{ jadwal.jam_selesai }}
                                </h4>
                                <p class="text-xs text-gray-500">
                                    Kelas {{ jadwal.kelas_min }}-{{ jadwal.kelas_max }} • {{ jadwal.lokasi || 'Lokasi belum diatur' }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Badges Status -->
                        <div class="flex gap-2">
                            <span v-if="statusWaktu === 'past'" class="inline-flex items-center gap-1 rounded bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                Terlewat
                            </span>
                            <span v-if="submittedSchedules[jadwal.id_jadwal]" class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Tersimpan
                            </span>
                        </div>
                    </div>

                    <!-- Legenda Singkat -->
                    <div class="px-6 py-2 bg-gray-50/50 border-b border-gray-100 flex gap-4 justify-end text-[10px] text-gray-400 uppercase font-bold tracking-wider">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Hadir</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-yellow-500"></span>Sakit</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span>Izin</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span>Alpha</span>
                    </div>

                    <!-- List Anggota -->
                    <div class="divide-y divide-gray-100">
                        <div v-if="getAnggotaByJadwal(jadwal).length === 0" class="p-6 text-center text-gray-400 text-sm italic">
                            Tidak ada anggota aktif untuk rentang kelas ini.
                        </div>

                        <div v-for="item in getAnggotaByJadwal(jadwal)" :key="item.id_anggota" class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                            <!-- Info Siswa -->
                            <div class="flex items-center gap-3 w-1/3">
                                <div class="h-8 w-8 flex-shrink-0 flex items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-500 border border-gray-200">
                                    {{ item.peserta?.nama_lengkap?.charAt(0) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-800 truncate">{{ item.peserta?.nama_lengkap }}</p>
                                    <p class="text-[10px] text-gray-500">Kelas {{ item.peserta?.tingkat_kelas }}</p>
                                </div>
                            </div>

                            <!-- Radio Button Absensi -->
                            <div v-if="form.absensi[jadwal.id_jadwal]" class="flex items-center gap-1">
                                <fieldset :disabled="submittedSchedules[jadwal.id_jadwal]" class="flex gap-1">
                                    <!-- Hadir -->
                                    <label class="cursor-pointer relative group">
                                        <input type="radio" :name="`att-${jadwal.id_jadwal}-${item.id_peserta}`" value="Hadir" v-model="form.absensi[jadwal.id_jadwal][item.peserta.id_peserta]" class="peer sr-only">
                                        <span class="block w-8 h-8 flex items-center justify-center text-[10px] font-bold border border-gray-200 rounded text-gray-400 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:border-emerald-600 hover:bg-emerald-50 peer-checked:hover:bg-emerald-600 transition-all">H</span>
                                        <!-- Tooltip -->
                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-800 text-white text-[10px] py-1 px-2 rounded opacity-90 whitespace-nowrap z-10 pointer-events-none">Hadir</span>
                                    </label>
                                    <!-- Sakit -->
                                    <label class="cursor-pointer relative group">
                                        <input type="radio" :name="`att-${jadwal.id_jadwal}-${item.id_peserta}`" value="Sakit" v-model="form.absensi[jadwal.id_jadwal][item.peserta.id_peserta]" class="peer sr-only">
                                        <span class="block w-8 h-8 flex items-center justify-center text-[10px] font-bold border border-gray-200 rounded text-gray-400 peer-checked:bg-yellow-500 peer-checked:text-white peer-checked:border-yellow-600 hover:bg-yellow-50 peer-checked:hover:bg-yellow-600 transition-all">S</span>
                                        <!-- Tooltip -->
                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-800 text-white text-[10px] py-1 px-2 rounded opacity-90 whitespace-nowrap z-10 pointer-events-none">Sakit</span>
                                    </label>
                                    <!-- Izin -->
                                    <label class="cursor-pointer relative group">
                                        <input type="radio" :name="`att-${jadwal.id_jadwal}-${item.id_peserta}`" value="Izin" v-model="form.absensi[jadwal.id_jadwal][item.peserta.id_peserta]" class="peer sr-only">
                                        <span class="block w-8 h-8 flex items-center justify-center text-[10px] font-bold border border-gray-200 rounded text-gray-400 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-600 hover:bg-blue-50 peer-checked:hover:bg-blue-600 transition-all">I</span>
                                        <!-- Tooltip -->
                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-800 text-white text-[10px] py-1 px-2 rounded opacity-90 whitespace-nowrap z-10 pointer-events-none">Izin</span>
                                    </label>
                                    <!-- Alpha -->
                                    <label class="cursor-pointer relative group">
                                        <input type="radio" :name="`att-${jadwal.id_jadwal}-${item.id_peserta}`" value="Alpha" v-model="form.absensi[jadwal.id_jadwal][item.peserta.id_peserta]" class="peer sr-only">
                                        <span class="block w-8 h-8 flex items-center justify-center text-[10px] font-bold border border-gray-200 rounded text-gray-400 peer-checked:bg-red-500 peer-checked:text-white peer-checked:border-red-600 hover:bg-red-50 peer-checked:hover:bg-red-600 transition-all">A</span>
                                        <!-- Tooltip -->
                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-800 text-white text-[10px] py-1 px-2 rounded opacity-90 whitespace-nowrap z-10 pointer-events-none">Alpha</span>
                                    </label>
                                </fieldset>
                            </div>
                        </div>
                    </div>

                    <!-- Footer / Submit Button -->
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end items-center gap-3">
                        <span v-if="submittedSchedules[jadwal.id_jadwal]" class="text-xs text-emerald-600 font-medium">
                            Data berhasil disimpan.
                        </span>
                        
                        <button 
                            @click="submitAbsensi(jadwal.id_jadwal)"
                            :disabled="statusWaktu === 'future' || getAnggotaByJadwal(jadwal).length === 0 || submittedSchedules[jadwal.id_jadwal]"
                            class="flex items-center gap-2 rounded-lg bg-[#213448] px-4 py-2 text-xs font-bold text-[#EAE0CF] shadow-sm hover:bg-[#547792] disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <span v-if="submittedSchedules[jadwal.id_jadwal]">Tersimpan</span>
                            <span v-else>Simpan Absensi</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>