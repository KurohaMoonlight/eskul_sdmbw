<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ModalFormAddAnggota from '@/Components/ModalFormAddAnggota.vue';

const props = defineProps({
    anggota: Array,
    idEskul: [Number, String],
    allPeserta: Array,
    // Tambahkan ini
    minKelas: [Number, String], 
    maxKelas: [Number, String]
});

// State
const searchQuery = ref('');
const showModal = ref(false);
const selectedAnggota = ref(null);

// Search Logic
const filteredAnggota = computed(() => {
    if (!searchQuery.value) return props.anggota;
    const lowerQuery = searchQuery.value.toLowerCase();
    
    return props.anggota.filter(item => {
        const nama = item.peserta?.nama_lengkap?.toLowerCase() || '';
        const kelas = item.peserta?.tingkat_kelas?.toLowerCase() || '';
        return nama.includes(lowerQuery) || kelas.includes(lowerQuery);
    });
});

// Actions
const openTambah = () => {
    selectedAnggota.value = null;
    showModal.value = true;
};

const openEdit = (item) => {
    selectedAnggota.value = item;
    showModal.value = true;
};

const deleteAnggota = (id) => {
    if (confirm('Yakin ingin mengeluarkan anggota ini dari eskul?')) {
        router.delete(`/admin/anggota/${id}`, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header & Search -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-xl font-bold text-[#213448]">Daftar Anggota</h3>
                <p class="text-sm text-[#94B4C1]">Kelola siswa yang tergabung dalam ekstrakurikuler ini.</p>
            </div>
            
            <div class="flex gap-3">
                <!-- Search Box -->
                <div class="relative">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Cari nama siswa..." 
                        class="w-full rounded-xl border border-[#94B4C1]/50 bg-white py-2.5 pl-10 pr-4 text-sm focus:border-[#213448] focus:outline-none focus:ring-1 focus:ring-[#213448]"
                    >
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Add Button -->
                <button 
                    @click="openTambah"
                    class="flex items-center gap-2 rounded-xl bg-[#213448] px-4 py-2.5 text-sm font-bold text-[#EAE0CF] transition hover:bg-[#547792] hover:shadow-lg active:scale-95"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Tambah Anggota</span>
                </button>
            </div>
        </div>

        <!-- Modern Table -->
        <div class="overflow-hidden rounded-2xl border border-[#94B4C1]/30 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#213448]/5 text-[#213448]">
                        <tr>
                            <th class="px-6 py-4 font-bold">No</th>
                            <th class="px-6 py-4 font-bold">Nama Siswa</th>
                            <th class="px-6 py-4 font-bold">Kelas</th>
                            <th class="px-6 py-4 font-bold">Jenis Kelamin</th>
                            <th class="px-6 py-4 font-bold">Tahun Ajaran</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#94B4C1]/20">
                        <tr 
                            v-for="(item, index) in filteredAnggota" 
                            :key="item.id_anggota"
                            class="group transition hover:bg-[#EAE0CF]/10"
                        >
                            <td class="px-6 py-4 font-medium text-[#94B4C1]">{{ index + 1 }}</td>
                            <td class="px-6 py-4 font-bold text-[#213448]">
                                {{ item.peserta?.nama_lengkap || 'Data Siswa Terhapus' }}
                            </td>
                            <td class="px-6 py-4">{{ item.peserta?.tingkat_kelas }}</td>
                            <td class="px-6 py-4">
                                <span v-if="item.peserta?.jenis_kelamin === 'L'" class="font-semibold text-blue-600">Laki-laki</span>
                                <span v-else class="font-semibold text-pink-600">Perempuan</span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-[#547792]">{{ item.tahun_ajaran }}</td>
                            <td class="px-6 py-4">
                                <span 
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold"
                                    :class="item.status_aktif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                >
                                    {{ item.status_aktif ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                    <button @click="openEdit(item)" class="rounded-lg p-1.5 text-[#94B4C1] hover:bg-[#547792] hover:text-white" title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteAnggota(item.id_anggota)" class="rounded-lg p-1.5 text-[#94B4C1] hover:bg-red-500 hover:text-white" title="Keluarkan">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredAnggota.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-[#94B4C1]">
                                    <svg class="h-10 w-10 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="mt-2 text-sm">Tidak ada anggota ditemukan.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ModalFormAddAnggota 
            :show="showModal"
            :anggotaData="selectedAnggota"
            :idEskul="idEskul"
            :allPeserta="allPeserta"
            :minKelas="props.minKelas" 
            :maxKelas="props.maxKelas"
            @close="showModal = false"
        />
    </div>
</template>