<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    prestasi: {
        type: Array,
        default: () => []
    },
    idEskul: [Number, String]
});

// Define Emits agar parent (EskulCardDetail) bisa menangkap event ini
const emit = defineEmits(['add', 'edit']);

const openTambah = () => {
    emit('add');
};

const openEdit = (item) => {
    emit('edit', item);
};

// Form Delete
const deleteForm = useForm({});

const deletePrestasi = (id) => {
    Swal.fire({
        title: 'Hapus Prestasi?',
        text: "Data prestasi ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteForm.delete(`/admin/prestasi/${id}`, {
                preserveScroll: true,
                onSuccess: () => Swal.fire('Terhapus!', 'Data prestasi berhasil dihapus.', 'success'),
                onError: () => Swal.fire('Gagal!', 'Gagal menghapus data.', 'error')
            });
        }
    });
};

// Helper Warna Tingkat
const getTingkatBadge = (tingkat) => {
    switch(tingkat) {
        case 'Kecamatan': return 'bg-gray-100 text-gray-700 border-gray-200';
        case 'Kabupaten': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'Provinsi': return 'bg-yellow-100 text-yellow-700 border-yellow-200';
        case 'Nasional': return 'bg-red-100 text-red-700 border-red-200';
        default: return 'bg-gray-50 text-gray-500';
    }
};
</script>

<template>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden w-full">
        <!-- Header -->
        <div class="bg-[#213448] px-6 py-4 flex items-center justify-between">
            <h3 class="text-[#EAE0CF] font-bold text-lg flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
                Prestasi & Penghargaan
            </h3>
            <button 
                @click="openTambah"
                class="text-xs bg-[#547792] text-white px-3 py-1.5 rounded hover:bg-white hover:text-[#213448] transition flex items-center gap-1 font-bold shadow-sm"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Prestasi
            </button>
        </div>

        <!-- Body: Grid Card -->
        <div class="p-6 bg-gray-50/50">
            
            <!-- Empty State -->
            <div v-if="prestasi.length === 0" class="flex flex-col items-center justify-center h-40 text-center border-2 border-dashed border-gray-200 rounded-xl">
                <div class="bg-gray-50 p-3 rounded-full mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <p class="text-gray-400 font-medium text-sm">Belum ada data prestasi yang tercatat.</p>
            </div>

            <!-- Grid List -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="item in prestasi" :key="item.id_prestasi" class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all group overflow-hidden relative flex flex-col h-full">
                    
                    <!-- Dekorasi Juara -->
                    <div class="absolute top-0 right-0 p-3 z-10">
                        <div class="flex items-center gap-1 bg-yellow-50 text-yellow-700 px-2 py-1 rounded text-[10px] font-bold border border-yellow-200 shadow-sm backdrop-blur-sm bg-opacity-90">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.699-3.181a1 1 0 011.827.954L17.144 7.64l1.323 1.323a1 1 0 01-1.414 1.414l-1.323-1.323-3.181 1.699L11 14.854V16a1 1 0 11-2 0v-1.146l-1.549-3.954-3.181-1.699-1.323 1.323a1 1 0 11-1.414-1.414l1.323-1.323-1.335-3.867a1 1 0 011.827-.954L6.046 4.323 10 2.747V3a1 1 0 010-1z" clip-rule="evenodd" />
                            </svg>
                            {{ item.juara_ke }}
                        </div>
                    </div>

                    <!-- Foto Prestasi (Thumbnail) -->
                    <!-- Jika ada foto, tampilkan. Jika tidak, placeholder pattern -->
                    <div class="h-32 w-full bg-gray-100 relative overflow-hidden">
                        <img 
                            v-if="item.foto_prestasi" 
                            :src="`/storage/${item.foto_prestasi}`" 
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            alt="Dokumentasi"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center bg-gray-50 text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-start justify-between mb-2">
                            <span :class="['text-[10px] uppercase font-bold px-2 py-0.5 rounded border', getTingkatBadge(item.tingkat)]">
                                {{ item.tingkat }}
                            </span>
                        </div>

                        <h4 class="text-base font-bold text-gray-800 line-clamp-2 mb-1 leading-tight" :title="item.nama_lomba">
                            {{ item.nama_lomba }}
                        </h4>
                        
                        <p class="text-xs text-gray-500 mb-4 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            {{ new Date(item.tanggal_lomba).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) }}
                        </p>

                        <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] text-gray-500 font-bold shrink-0">
                                    {{ item.peserta?.nama_lengkap?.charAt(0) || 'T' }}
                                </div>
                                <span class="text-xs text-gray-600 font-medium truncate" :title="item.peserta?.nama_lengkap || 'Tim Eskul'">
                                    {{ item.peserta?.nama_lengkap || 'Tim Eskul' }}
                                </span>
                            </div>

                            <!-- Actions (Edit & Delete) -->
                            <!-- Menggunakan opactity-100 di mobile, hover di desktop sesuai request sebelumnya -->
                            <div class="flex gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="openEdit(item)" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1.5 rounded hover:bg-blue-100 transition" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <button @click="deletePrestasi(item.id_prestasi)" class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded hover:bg-red-100 transition" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>