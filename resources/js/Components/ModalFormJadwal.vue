<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    show: Boolean,
    jadwalData: Object,      // Data edit (null jika tambah baru)
    idEskul: [Number, String] // ID Eskul (Wajib untuk tambah baru)
});

const emit = defineEmits(['close']);

// Daftar Hari
const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

const form = useForm({
    id_eskul: '',
    hari: [], // Array untuk menampung checklist hari
    jam_mulai: '',
    jam_selesai: '',
    lokasi: '',
    kelas_min: '1',
    kelas_max: '6',
});

// Watcher: Reset/Isi form saat modal dibuka
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        // Clear errors saat modal dibuka
        form.clearErrors();

        if (props.jadwalData) {
            // Mode Edit
            form.id_eskul = props.jadwalData.id_eskul;
            // Bungkus string hari dari DB ke array agar checklist terbaca
            form.hari = [props.jadwalData.hari]; 
            form.jam_mulai = props.jadwalData.jam_mulai;
            form.jam_selesai = props.jadwalData.jam_selesai;
            form.lokasi = props.jadwalData.lokasi;
            form.kelas_min = String(props.jadwalData.kelas_min);
            form.kelas_max = String(props.jadwalData.kelas_max);
        } else {
            // Mode Tambah
            form.reset();
            // PASTIKAN ID ESKUL TERISI DI SINI
            form.id_eskul = props.idEskul; 
            form.hari = [];
            form.kelas_min = '1';
            form.kelas_max = '6';
        }
    }
});

const submit = () => {
    // Pastikan id_eskul terisi sebelum submit (double check untuk mode tambah)
    if (!form.id_eskul && props.idEskul) {
        form.id_eskul = props.idEskul;
    }

    // Logic URL & Method
    const url = props.jadwalData ? `/admin/jadwal/${props.jadwalData.id_jadwal}` : '/admin/jadwal';
    const method = props.jadwalData ? 'put' : 'post';

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
        onError: (errors) => {
            console.error("Gagal simpan jadwal:", errors);
        }
    });
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <!-- Overlay -->
        <div v-if="show" class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-[#213448]/80 backdrop-blur-sm p-4" @click.self="$emit('close')">
            
            <!-- Modal Card -->
            <div class="relative w-full max-w-lg transform rounded-2xl bg-white shadow-2xl transition-all border border-[#94B4C1]">
                
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-[#94B4C1]/50 px-6 py-4">
                    <h3 class="text-xl font-bold text-[#213448]">
                        {{ jadwalData ? 'Edit Jadwal Latihan' : 'Atur Jadwal Latihan' }}
                    </h3>
                    <button @click="$emit('close')" class="rounded-lg p-1 text-[#547792] hover:bg-[#213448]/10 hover:text-[#213448] transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="p-6 space-y-5">
                    
                    <!-- Checklist Hari -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-[#213448]">Pilih Hari</label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <label 
                                v-for="day in days" 
                                :key="day" 
                                class="flex cursor-pointer items-center space-x-2 rounded-lg border border-[#94B4C1]/50 bg-white p-2 transition hover:bg-[#94B4C1]/10"
                                :class="{'bg-[#213448]/10 ring-1 ring-[#213448]': form.hari.includes(day)}"
                            >
                                <input 
                                    type="checkbox" 
                                    :value="day" 
                                    v-model="form.hari"
                                    class="h-4 w-4 rounded border-[#94B4C1] text-[#213448] focus:ring-[#547792]"
                                >
                                <span class="text-sm font-medium text-[#213448]">{{ day }}</span>
                            </label>
                        </div>
                        <span v-if="form.errors.hari" class="text-xs text-red-500">{{ form.errors.hari }}</span>
                    </div>

                    <!-- Jam Mulai & Selesai (Side by Side) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-[#213448]">Jam Mulai</label>
                            <input 
                                type="time" 
                                v-model="form.jam_mulai"
                                class="w-full rounded-lg border border-[#94B4C1] bg-white p-2.5 text-[#213448] focus:border-[#213448] focus:outline-none focus:ring-2 focus:ring-[#547792]/20"
                            >
                            <span v-if="form.errors.jam_mulai" class="text-xs text-red-500">{{ form.errors.jam_mulai }}</span>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-[#213448]">Jam Selesai</label>
                            <input 
                                type="time" 
                                v-model="form.jam_selesai"
                                class="w-full rounded-lg border border-[#94B4C1] bg-white p-2.5 text-[#213448] focus:border-[#213448] focus:outline-none focus:ring-2 focus:ring-[#547792]/20"
                            >
                            <span v-if="form.errors.jam_selesai" class="text-xs text-red-500">{{ form.errors.jam_selesai }}</span>
                        </div>
                    </div>

                    <!-- Lokasi -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-[#213448]">Lokasi Latihan</label>
                        <input 
                            type="text" 
                            v-model="form.lokasi"
                            class="w-full rounded-lg border border-[#94B4C1] bg-white p-3 text-[#213448] placeholder-[#94B4C1] focus:border-[#213448] focus:outline-none focus:ring-2 focus:ring-[#547792]/20"
                            placeholder="Contoh: Halaman Sekolah"
                        >
                        <span v-if="form.errors.lokasi" class="text-xs text-red-500">{{ form.errors.lokasi }}</span>
                    </div>

                    <!-- Target Kelas (Min - Max) -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-[#213448]">Kelas Min</label>
                            <select 
                                v-model="form.kelas_min"
                                class="w-full rounded-lg border border-[#94B4C1] bg-white p-2.5 text-[#213448] focus:border-[#213448] focus:outline-none focus:ring-2 focus:ring-[#547792]/20"
                            >
                                <option v-for="i in 6" :key="i" :value="String(i)">Kelas {{ i }}</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-[#213448]">Kelas Max</label>
                            <select 
                                v-model="form.kelas_max"
                                class="w-full rounded-lg border border-[#94B4C1] bg-white p-2.5 text-[#213448] focus:border-[#213448] focus:outline-none focus:ring-2 focus:ring-[#547792]/20"
                            >
                                <option v-for="i in 6" :key="i" :value="String(i)">Kelas {{ i }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#94B4C1]/50">
                        <button 
                            type="button" 
                            @click="$emit('close')"
                            class="rounded-lg px-4 py-2 text-sm font-bold text-[#547792] transition hover:bg-[#213448]/5 hover:text-[#213448]"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="flex items-center gap-2 rounded-lg bg-[#213448] px-6 py-2 text-sm font-bold text-[#EAE0CF] shadow-lg transition hover:bg-[#547792] hover:shadow-xl disabled:opacity-70"
                        >
                            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-[#EAE0CF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Jadwal' }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </Transition>
</template>