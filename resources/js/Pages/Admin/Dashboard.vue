<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import ModalFormPembimbing from '@/Components/ModalFormPembimbing.vue';
import ModalFormEskul from '@/Components/ModalFormEskul.vue'; // Import Modal Eskul
import TablePembimbing from '@/Components/TablePembimbing.vue';
import CardEskul from '@/Components/CardEskul.vue';

// Props dari Controller
const props = defineProps({
    pembimbings: {
        type: Array,
        default: () => [],
    },
    eskuls: {
        type: Array,
        default: () => [],
    },
});

// State untuk Modal Pembimbing
const showModalPembimbing = ref(false);
const selectedPembimbing = ref(null);

// State untuk Modal Eskul
const showModalEskul = ref(false);

// Buka Modal Tambah Pembimbing (Reset Data)
const openTambahPembimbing = () => {
    selectedPembimbing.value = null;
    showModalPembimbing.value = true;
};

// Buka Modal Edit Pembimbing (Isi Data)
const openEditPembimbing = (item) => {
    selectedPembimbing.value = item;
    showModalPembimbing.value = true;
};

// Buka Modal Tambah Eskul
const openTambahEskul = () => {
    showModalEskul.value = true;
};
</script>

<template>
    <Head title="Dashboard Admin" />

    <div class="min-h-screen bg-[#EAE0CF]">
        
        <!-- Navbar -->
        <Navbar>
            <!-- Tombol Trigger Modal Pembimbing -->
            <button 
                @click="openTambahPembimbing"
                class="rounded-lg bg-[#547792] px-4 py-2 text-sm font-bold text-[#EAE0CF] shadow-md transition-all hover:bg-[#EAE0CF] hover:text-[#213448] hover:shadow-lg active:scale-95"
            >
                + Tambah Pembimbing
            </button>
            
            <!-- Link Data Eskul DIHAPUS sesuai permintaan -->
        </Navbar>

        <!-- Main Content -->
        <main class="py-10">
            <!-- Container dibuat full width dengan padding horizontal yang proporsional -->
            <div class="w-full px-4 sm:px-6 lg:px-10">
                
                <!-- Layout Grid: Kiri (Tabel) - Kanan (Cards) -->
                <div class="grid grid-cols-1 gap-10 xl:grid-cols-4">
                    
                    <!-- KOLOM KIRI: Tabel Pembimbing -->
                    <div class="space-y-4 xl:col-span-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-[#213448]">Daftar Pembimbing</h2>
                        </div>
                        
                        <!-- Component Tabel -->
                        <TablePembimbing 
                            :pembimbings="props.pembimbings" 
                            @edit="openEditPembimbing"
                        />
                    </div>

                    <!-- KOLOM KANAN: Card Eskul -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-[#213448]">Ekstrakurikuler Aktif</h2>
                            
                            <!-- Tombol Tambah Eskul -->
                            <button 
                                @click="openTambahEskul"
                                class="rounded-lg bg-[#213448] px-3 py-1.5 text-xs font-bold text-[#EAE0CF] shadow-sm transition hover:bg-[#547792] active:scale-95"
                            >
                                + Tambah
                            </button>
                        </div>

                        <!-- Looping Card Eskul -->
                        <div class="flex flex-col gap-4">
                            <CardEskul 
                                v-for="eskul in props.eskuls" 
                                :key="eskul.id_eskul" 
                                :eskul="eskul" 
                                @click="$inertia.visit(`/admin/eskul/${eskul.id_eskul}`)"
                            />

                            <!-- State Kosong untuk Eskul -->
                            <div v-if="props.eskuls.length === 0" class="rounded-lg border-2 border-dashed border-[#94B4C1] p-6 text-center text-[#547792]">
                                <p class="text-sm font-medium">Belum ada data eskul.</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </main>

        <!-- Modal Form Pembimbing -->
        <ModalFormPembimbing 
            :show="showModalPembimbing" 
            :pembimbingData="selectedPembimbing" 
            @close="showModalPembimbing = false" 
        />

        <!-- Modal Form Eskul -->
        <ModalFormEskul 
            :show="showModalEskul"
            :pembimbings="props.pembimbings" 
            :eskulData="null"
            @close="showModalEskul = false"
        />

    </div>
</template>