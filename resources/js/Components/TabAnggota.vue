<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ModalFormAddAnggota from '@/Components/ModalFormAddAnggota.vue'; // Form Add/Edit

const props = defineProps({
    anggota: {
        type: Array,
        default: () => [],
    },
    idEskul: [Number, String], 
});

const showDetailModal = ref(false);
const showFormModal = ref(false);
const selectedAnggota = ref(null);

// Buka Modal Detail (View Only)
const openDetail = (item) => {
    selectedAnggota.value = item;
    showDetailModal.value = true;
};

// Buka Modal Tambah
const openAdd = () => {
    selectedAnggota.value = null; // Reset
    showFormModal.value = true;
};

// Buka Modal Edit
const openEdit = (item) => {
    selectedAnggota.value = item; // Isi data untuk diedit
    showFormModal.value = true;
};

// Hapus Anggota
const deleteAnggota = (id) => {
    if (confirm('Yakin ingin menghapus anggota ini? Data siswa di master tidak akan terhapus.')) {
        router.delete(`/admin/anggota/${id}`, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <div class="rounded-xl border border-[#94B4C1] bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-[#213448]">Daftar Anggota</h3>
            
            <button 
                @click="openAdd"
                class="rounded-lg bg-[#547792] px-3 py-1.5 text-xs font-bold text-[#EAE0CF] hover:bg-[#213448] transition-colors"
            >
                + Tambah Anggota
            </button>
        </div>

        <div v-if="anggota && anggota.length > 0" class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#213448]/5 text-[#213448]">
                    <tr>
                        <th class="px-4 py-3 font-bold">No</th>
                        <th class="px-4 py-3 font-bold">Nama Peserta</th>
                        <th class="px-4 py-3 font-bold">Kelas</th>
                        <th class="px-4 py-3 font-bold">Tahun Ajaran</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                        <th class="px-4 py-3 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#94B4C1]/30">
                    <tr 
                        v-for="(a, index) in anggota" 
                        :key="a.id_anggota"
                        class="hover:bg-[#EAE0CF]/20 transition-colors"
                    >
                        <td class="px-4 py-3 text-center w-12">{{ index + 1 }}</td>
                        <td class="px-4 py-3 font-medium">{{ a.peserta ? a.peserta.nama_lengkap : '-' }}</td>
                        <td class="px-4 py-3">{{ a.peserta ? a.peserta.tingkat_kelas : '-' }}</td>
                        <td class="px-4 py-3">{{ a.tahun_ajaran }}</td>
                        <td class="px-4 py-3">
                            <span :class="a.status_aktif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="rounded px-2 py-0.5 text-xs font-bold">
                                {{ a.status_aktif ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center flex justify-center gap-2">
                            <!-- Tombol Detail -->
                            <button @click="openDetail(a)" class="text-[#547792] hover:text-[#213448]" title="Lihat Detail">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <!-- Tombol Edit -->
                            <button @click="openEdit(a)" class="text-[#547792] hover:text-[#213448]" title="Edit">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <!-- Tombol Hapus -->
                            <button @click="deleteAnggota(a.id_anggota)" class="text-[#547792] hover:text-red-500" title="Hapus">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="py-8 text-center text-[#94B4C1] italic">
            Belum ada anggota terdaftar.
        </div>

        <!-- Modal Detail (Read Only) -->
        <ModalFormAnggota 
            :show="showDetailModal"
            :anggotaData="selectedAnggota"
            @close="showDetailModal = false"
        />

        <!-- Modal Form (Add/Edit) -->
        <ModalFormAddAnggota 
            :show="showFormModal"
            :idEskul="idEskul"
            :anggotaData="selectedAnggota"
            @close="showFormModal = false"
        />
    </div>
</template>