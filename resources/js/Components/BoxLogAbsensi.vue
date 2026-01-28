<script setup>
import { ref, watch, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { debounce } from 'lodash'; 

const props = defineProps({
    logs: {
        type: Object,
        default: () => ({ data: [], links: [] }) 
    },
    // Data Rekap
    summary: {
        type: Object,
        default: () => ({
            total_pertemuan: 0,
            avg_kehadiran: 0, 
            total_sakit: 0,
            total_izin: 0,
            total_alpha: 0
        })
    },
    filters: {
        type: Object,
        default: () => ({ search: '', start_date: '', end_date: '', status: '', score_mode: '' })
    },
    idEskul: [Number, String]
});

// Computed safe summary
const s = computed(() => {
    return {
        total_pertemuan: props.summary?.total_pertemuan ?? 0,
        avg_kehadiran: Number(props.summary?.avg_kehadiran ?? 0),
        total_sakit: props.summary?.total_sakit ?? 0,
        total_izin: props.summary?.total_izin ?? 0,
        total_alpha: props.summary?.total_alpha ?? 0,
    }
});

// State Lokal untuk Filter
const search = ref(props.filters.search || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const statusFilter = ref(props.filters.status || '');
const scoreFilter = ref(props.filters.score_mode || ''); // Filter Nilai Baru

const applyFilter = debounce(() => {
    router.get(
        `/pembimbing/eskul/${props.idEskul}`, 
        { 
            search: search.value,
            start_date: startDate.value,
            end_date: endDate.value,
            status: statusFilter.value,
            score_mode: scoreFilter.value, // Kirim parameter filter nilai
            mode: 'log_filter' 
        },
        { 
            preserveState: true, 
            preserveScroll: true, 
            only: ['logs', 'logSummary', 'filters'] 
        }
    );
}, 500);

// Watcher untuk semua filter
watch([search, statusFilter, startDate, endDate, scoreFilter], applyFilter);

const printLog = () => {
    const params = new URLSearchParams({
        start_date: startDate.value,
        end_date: endDate.value,
        id_eskul: props.idEskul,
        status: statusFilter.value,
        score_mode: scoreFilter.value
    }).toString();
    
    window.open(`/admin/absensi/print?${params}`, '_blank');
};

const getStatusBadge = (status) => {
    switch(status) {
        case 'Hadir': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'Sakit': return 'bg-yellow-100 text-yellow-700 border-yellow-200';
        case 'Izin': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'Alpha': return 'bg-red-100 text-red-700 border-red-200';
        default: return 'bg-gray-100 text-gray-700';
    }
};
</script>

<template>
    <div class="space-y-6">
        
        <!-- 1. REKAPITULASI CEPAT (STAT CARDS) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Card 1: Total Pertemuan -->
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-center items-center text-center">
                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Total Pertemuan</span>
                <span class="text-2xl font-bold text-[#213448] mt-1">{{ s.total_pertemuan }}</span>
                <span class="text-[10px] text-gray-400">Sesi terlaksana</span>
            </div>

            <!-- Card 2: Rata-rata Kehadiran -->
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-center items-center text-center relative overflow-hidden">
                <div class="absolute bottom-0 left-0 h-1.5 bg-gray-100 w-full">
                    <div class="h-full bg-emerald-500 transition-all duration-500" :style="{ width: s.avg_kehadiran + '%' }"></div>
                </div>
                
                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Rata-rata Hadir</span>
                <span class="text-2xl font-bold text-emerald-600 mt-1">{{ s.avg_kehadiran }}%</span>
                <span class="text-[10px] text-gray-400">Partisipasi siswa</span>
            </div>

            <!-- Card 3: Ketidakhadiran (Sakit/Izin) -->
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-center items-center text-center">
                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Sakit / Izin</span>
                <div class="flex gap-2 mt-1">
                    <span class="text-lg font-bold text-yellow-600">{{ s.total_sakit }}S</span>
                    <span class="text-gray-300">|</span>
                    <span class="text-lg font-bold text-blue-600">{{ s.total_izin }}I</span>
                </div>
                <span class="text-[10px] text-gray-400">Total akumulasi</span>
            </div>

            <!-- Card 4: Alpha -->
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-center items-center text-center">
                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Tanpa Keterangan</span>
                <span class="text-2xl font-bold text-red-600 mt-1">{{ s.total_alpha }}</span>
                <span class="text-[10px] text-gray-400">Total Alpha</span>
            </div>
        </div>

        <!-- 2. MAIN BOX LOG (FILTER & TABLE) -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            
            <!-- Toolbar Header -->
            <div class="bg-[#213448] px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-[#EAE0CF]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="font-bold text-lg">Riwayat Absensi & Nilai</h3>
                </div>

                <!-- Action Tools -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <!-- Search -->
                    <div class="relative flex-1 md:flex-none">
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Cari Siswa..." 
                            class="pl-8 pr-3 py-1.5 w-full md:w-48 text-sm rounded-lg border-none bg-white/10 text-white placeholder-gray-400 focus:ring-2 focus:ring-[#547792]"
                        >
                        <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Date Range -->
                    <input 
                        v-model="startDate"
                        type="date" 
                        class="py-1.5 px-2 text-sm rounded-lg border-none bg-white/10 text-white placeholder-gray-400 focus:ring-2 focus:ring-[#547792] w-32"
                    >
                    <span class="text-gray-400">-</span>
                    <input 
                        v-model="endDate"
                        type="date" 
                        class="py-1.5 px-2 text-sm rounded-lg border-none bg-white/10 text-white placeholder-gray-400 focus:ring-2 focus:ring-[#547792] w-32"
                    >

                    <!-- Status Filter -->
                    <select v-model="statusFilter" class="py-1.5 px-2 text-sm rounded-lg border-none bg-white/10 text-white focus:ring-2 focus:ring-[#547792]">
                        <option value="" class="text-gray-800">Semua Status</option>
                        <option value="Hadir" class="text-gray-800">Hadir</option>
                        <option value="Sakit" class="text-gray-800">Sakit</option>
                        <option value="Izin" class="text-gray-800">Izin</option>
                        <option value="Alpha" class="text-gray-800">Alpha</option>
                    </select>

                    <!-- NEW: Score Filter (FILTER NILAI BARU) -->
                    <select v-model="scoreFilter" class="py-1.5 px-2 text-sm rounded-lg border-none bg-white/10 text-white focus:ring-2 focus:ring-[#547792]">
                        <option value="" class="text-gray-800">Filter Nilai</option>
                        <option value="highest" class="text-gray-800">Tertinggi</option>
                        <option value="lowest" class="text-gray-800">Terendah</option>
                        <option value="under_70" class="text-gray-800">Di Bawah 70</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-3 border-b border-gray-100">Tanggal & Sesi</th>
                            <th class="px-6 py-3 border-b border-gray-100">Siswa</th>
                            <th class="px-6 py-3 border-b border-gray-100">Status</th>
                            <!-- Kolom Nilai Baru -->
                            <th class="px-2 py-3 border-b border-gray-100 text-center" title="Teknik">Tek</th>
                            <th class="px-2 py-3 border-b border-gray-100 text-center" title="Disiplin">Dis</th>
                            <th class="px-2 py-3 border-b border-gray-100 text-center" title="Kerjasama">Ker</th>
                            <th class="px-6 py-3 border-b border-gray-100">Catatan Harian</th>
                            <!-- End Kolom Nilai -->
                            <th class="px-6 py-3 border-b border-gray-100">Materi Kegiatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="logs.data.length === 0">
                            <td colspan="8" class="px-6 py-8 text-center text-gray-400 italic text-sm">
                                Tidak ada data absensi/nilai ditemukan.
                            </td>
                        </tr>
                        
                        <tr v-for="log in logs.data" :key="log.id_absensi" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3">
                                <p class="text-sm font-bold text-gray-700">
                                    {{ new Date(log.kegiatan?.tanggal).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }) }}
                                </p>
                                <span class="text-[10px] text-gray-400">
                                    Input: {{ new Date(log.waktu_input).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#547792]/10 text-[#547792] flex items-center justify-center text-xs font-bold">
                                        {{ log.peserta?.nama_lengkap?.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ log.peserta?.nama_lengkap }}</p>
                                        <p class="text-[10px] text-gray-500">Kelas {{ log.peserta?.tingkat_kelas }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase', getStatusBadge(log.status)]">
                                    {{ log.status }}
                                </span>
                            </td>

                            <!-- Menampilkan Nilai (Read Only) -->
                            <td class="px-2 py-3 text-center text-sm text-gray-700 font-medium">
                                {{ log.nilai_harian?.skor_teknik ?? '-' }}
                            </td>
                            <td class="px-2 py-3 text-center text-sm text-gray-700 font-medium">
                                {{ log.nilai_harian?.skor_disiplin ?? '-' }}
                            </td>
                            <td class="px-2 py-3 text-center text-sm text-gray-700 font-medium">
                                {{ log.nilai_harian?.skor_kerjasama ?? '-' }}
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-xs text-gray-500 italic max-w-[150px] truncate" :title="log.nilai_harian?.catatan_harian">
                                    {{ log.nilai_harian?.catatan_harian || '-' }}
                                </p>
                            </td>

                            <td class="px-6 py-3">
                                <p class="text-xs text-gray-600 line-clamp-1 max-w-[200px]" :title="log.kegiatan?.materi_kegiatan">
                                    {{ log.kegiatan?.materi_kegiatan || '-' }}
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer: Pagination & Print -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
                
                <!-- Info Data -->
                <p class="text-xs text-gray-500">
                    Menampilkan <span class="font-bold">{{ logs.from || 0 }}</span> - <span class="font-bold">{{ logs.to || 0 }}</span> dari <span class="font-bold">{{ logs.total }}</span> data
                </p>

                <div class="flex items-center gap-4">
                    <!-- Pagination Links -->
                    <div class="flex gap-1" v-if="logs.links && logs.links.length > 3">
                        <template v-for="(link, key) in logs.links" :key="key">
                            <Link 
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1 text-xs rounded border transition-colors"
                                :class="link.active ? 'bg-[#213448] text-[#EAE0CF] border-[#213448]' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-100'"
                                preserve-scroll
                                preserve-state
                            >
                                <span v-html="link.label"></span>
                            </Link>
                        </template>
                    </div>

                    <!-- Tombol Download Excel -->
                    <button 
                        @click="printLog"
                        class="flex items-center gap-2 rounded-lg bg-emerald-600 border border-emerald-600 px-4 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition"
                        title="Download Laporan Excel"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download Excel
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>