<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ModalFormJadwal from '@/Components/ModalFormJadwal.vue';

const props = defineProps({
    // Tambahkan default value agar tidak error jika data null
    jadwal: {
        type: Array,
        default: () => [],
    },
    idEskul: [Number, String]
});

// State Modal
const showModal = ref(false);
const selectedJadwal = ref(null);

// Buka Modal Tambah
const openTambah = () => {
    selectedJadwal.value = null;
    showModal.value = true;
};

// Buka Modal Edit
const openEdit = (item) => {
    selectedJadwal.value = item;
    showModal.value = true;
};

// Hapus Jadwal
const deleteJadwal = (id) => {
    if (confirm('Yakin ingin menghapus jadwal ini?')) {
        router.delete(`/admin/jadwal/${id}`, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <div class="rounded-xl border border-[#94B4C1] bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-[#213448]">Jadwal Latihan</h3>
            
            <button 
                @click="openTambah"
                class="rounded-lg bg-[#547792] px-3 py-1.5 text-xs font-bold text-[#EAE0CF] hover:bg-[#213448] transition-colors"
            >
                + Atur Jadwal
            </button>
        </div>

        <!-- Debugging: Hapus baris ini jika data sudah muncul -->
        <!-- <pre class="mb-4 text-xs text-red-500">{{ jadwal }}</pre> -->

        <!-- Cek length hanya jika jadwal ada (truthy) -->
        <div v-if="jadwal && jadwal.length > 0" class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#213448]/5 text-[#213448]">
                    <tr>
                        <th class="px-4 py-3 font-bold">Hari</th>
                        <th class="px-4 py-3 font-bold">Jam</th>
                        <th class="px-4 py-3 font-bold">Lokasi</th>
                        <th class="px-4 py-3 font-bold">Kelas</th>
                        <th class="px-4 py-3 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#94B4C1]/30">
                    <tr v-for="j in jadwal" :key="j.id_jadwal">
                        <td class="px-4 py-3 font-medium">{{ j.hari }}</td>
                        <td class="px-4 py-3">{{ j.jam_mulai }} - {{ j.jam_selesai }}</td>
                        <td class="px-4 py-3">{{ j.lokasi }}</td>
                        <td class="px-4 py-3">Kelas {{ j.kelas_min }} - {{ j.kelas_max }}</td>
                        <td class="px-4 py-3 text-center flex justify-center gap-2">
                            <button @click="openEdit(j)" class="text-[#547792] hover:text-[#213448]" title="Edit Jadwal">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button @click="deleteJadwal(j.id_jadwal)" class="text-[#547792] hover:text-red-500" title="Hapus Jadwal">
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
            Belum ada jadwal latihan yang diatur.
        </div>

        <ModalFormJadwal 
            :show="showModal"
            :jadwalData="selectedJadwal"
            :idEskul="idEskul"
            @close="showModal = false"
        />
    </div>
</template>