<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import TabJadwal from '@/Components/TabJadwal.vue';
import TabAnggota from '@/Components/TabAnggota.vue';
import ModalFormEskul from '@/Components/ModalFormEskul.vue';
import Footer from '../../../Components/Footer.vue';

const props = defineProps({
    eskul: {
        type: Object,
        required: true
    },
    pembimbings: {
        type: Array,
        default: () => []
    },
    // Tangkap data peserta dari Controller
    allPeserta: {
        type: Array,
        default: () => []
    }
});

const activeTab = ref('anggota'); // Default tab (bisa diubah sesuai selera)
const showEditModal = ref(false);

const openEditInfo = () => {
    showEditModal.value = true;
};
</script>

<template>
    <Head :title="'Detail ' + (props.eskul?.nama_eskul || 'Eskul')" />

    <div class="min-h-screen bg-[#FFF]">
        <Navbar>
            <Link href="/admin/dashboard" class="flex items-center gap-2 text-sm font-bold text-[#94B4C1] hover:text-[#FFF]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </Link>
        </Navbar>

        <main class="py-10" v-if="props.eskul">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- HEADER INFO ESKUL -->
                <div class="mb-8 rounded-2xl bg-[#213448] p-6 text-[#EAE0CF] shadow-lg md:p-10 relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-[#547792] opacity-20 blur-3xl z-0 pointer-events-none"></div>
                    
                    <div class="relative z-10 flex flex-col justify-between gap-4 md:flex-row md:items-end">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold md:text-4xl">{{ props.eskul.nama_eskul }}</h1>
                            <p class="mt-2 text-[#94B4C1] max-w-2xl">{{ props.eskul.deskripsi || 'Tidak ada deskripsi.' }}</p>
                            
                            <div class="mt-6 flex flex-wrap gap-4">
                                <div class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-1.5 backdrop-blur-md">
                                    <svg class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="text-sm font-medium">{{ props.eskul.pembimbing?.nama_lengkap || 'Belum ada pembimbing' }}</span>
                                </div>
                                <div class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-1.5 backdrop-blur-md">
                                    <svg class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="text-sm font-medium">Kelas {{ props.eskul.jenjang_kelas_min }} - {{ props.eskul.jenjang_kelas_max }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative z-20 shrink-0">
                             <button type="button" @click="openEditInfo" class="cursor-pointer rounded-lg bg-[#EAE0CF]/20 px-4 py-2 text-sm font-bold transition hover:bg-[#EAE0CF] hover:text-[#213448] active:scale-95 shadow-sm">
                                Edit Info
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TABS NAVIGATION -->
                <div class="mb-6 border-b border-[#94B4C1]/50">
                    <nav class="-mb-px flex gap-6">
                        <button @click="activeTab = 'jadwal'" :class="activeTab === 'jadwal' ? 'border-[#213448] text-[#213448]' : 'border-transparent text-[#547792] hover:text-[#213448]'" class="border-b-4 px-1 py-4 text-sm font-bold transition-colors">
                            Jadwal Latihan
                        </button>
                        <button @click="activeTab = 'anggota'" :class="activeTab === 'anggota' ? 'border-[#213448] text-[#213448]' : 'border-transparent text-[#547792] hover:text-[#213448]'" class="border-b-4 px-1 py-4 text-sm font-bold transition-colors">
                            Anggota
                        </button>
                    </nav>
                </div>

                <!-- TAB CONTENT -->
                <div class="min-h-[400px]">
                    <TabJadwal v-if="activeTab === 'jadwal'" :jadwal="props.eskul.jadwal" :idEskul="props.eskul.id_eskul" :limitMin="props.eskul.jenjang_kelas_min" :limitMax="props.eskul.jenjang_kelas_max" />
                    
                    <!-- PASSING PROPS allPeserta KE SINI -->
                    <TabAnggota 
                        v-if="activeTab === 'anggota'" 
                        :anggota="props.eskul.anggota_eskul" 
                        :idEskul="props.eskul.id_eskul"
                        :allPeserta="props.allPeserta" 
                        :minKelas="props.eskul.jenjang_kelas_min"  
                        :maxKelas="props.eskul.jenjang_kelas_max"
                    />

                    <div v-if="activeTab === 'kegiatan'" class="rounded-xl border border-[#94B4C1] bg-white p-8 text-center">
                        <p class="text-[#547792] italic">Modul Kegiatan belum tersedia.</p>
                    </div>
                </div>
            </div>
        </main>

        <ModalFormEskul 
            v-if="showEditModal"
            :show="showEditModal"
            :pembimbings="props.pembimbings" 
            :eskulData="props.eskul" 
            @close="showEditModal = false"
        />
    </div>
     <Footer/>
</template>