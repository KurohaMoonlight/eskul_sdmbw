<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    pembimbings: {
        type: Array,
        default: () => []
    },
    eskulData: {
        type: Object,
        default: null
    }, 
});

const emit = defineEmits(['close']);

// Ubah id_pembimbing jadi array 'pembimbings'
const form = useForm({
    nama_eskul: '',
    pembimbings: [''], // Array untuk menampung banyak ID
    deskripsi: '',
    jenjang_kelas_min: '1',
    jenjang_kelas_max: '6',
});

// Computed untuk cek mode edit
const isEditMode = computed(() => !!props.eskulData);

// Helper tambah slot pembimbing
const addPembimbing = () => {
    form.pembimbings.push('');
};

// Helper hapus slot pembimbing
const removePembimbing = (index) => {
    // Sisakan minimal 1 slot
    if (form.pembimbings.length > 1) {
        form.pembimbings.splice(index, 1);
    }
};

// Logika Filter: Cek apakah pembimbing sudah dipilih di slot lain
const isPembimbingAvailable = (pembimbingId, currentIndex) => {
    return !form.pembimbings.some((selectedId, index) => {
        // Abaikan slot saat ini (agar nilai yang sedang dipilih tetap muncul di dropdown sendiri)
        if (index === currentIndex) return false;
        // Cek kesamaan nilai (loose equality == untuk handle string/number)
        return selectedId == pembimbingId;
    });
};

// Watcher dengan immediate: true agar form terisi saat modal muncul
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        if (props.eskulData) {
            // MODE EDIT
            form.nama_eskul = props.eskulData.nama_eskul || '';
            form.deskripsi = props.eskulData.deskripsi || '';
            form.jenjang_kelas_min = String(props.eskulData.jenjang_kelas_min || '1');
            form.jenjang_kelas_max = String(props.eskulData.jenjang_kelas_max || '6');

            // Logika untuk menangani Multiple Pembimbing
            // Cek apakah data dari backend sudah support array (future-proof) atau masih single ID
            if (props.eskulData.pembimbings && Array.isArray(props.eskulData.pembimbings)) {
                // Jika backend mengirim array relasi, ambil ID-nya saja
                form.pembimbings = props.eskulData.pembimbings.map(p => p.id_pembimbing);
            } else if (props.eskulData.id_pembimbing) {
                // Jika masih single ID, bungkus dalam array agar UI tetap jalan
                form.pembimbings = [props.eskulData.id_pembimbing];
            } else {
                form.pembimbings = [''];
            }

            // Jika array kosong (data korup), inisialisasi minimal 1
            if (form.pembimbings.length === 0) form.pembimbings = [''];

        } else {
            // MODE TAMBAH
            form.reset();
            form.pembimbings = ['']; // Default 1 slot kosong
            form.jenjang_kelas_min = '1';
            form.jenjang_kelas_max = '6';
        }
    }
}, { immediate: true });

const submit = () => {
    const url = props.eskulData ? `/admin/eskul/${props.eskulData.id_eskul}` : '/admin/eskul';
    const method = props.eskulData ? 'put' : 'post';

    // Form kini mengirim 'pembimbings' sebagai array ke controller
    form[method](url, {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-fade-in">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border-t-4 border-[#213448] transform transition-all scale-100 max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-extrabold text-[#213448]">
                    {{ eskulData ? 'Edit Data Eskul' : 'Tambah Eskul Baru' }}
                </h3>
                <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                
                <!-- Nama Eskul -->
                <div class="space-y-1">
                    <label class="block text-sm font-bold text-[#213448]">Nama Ekstrakurikuler</label>
                    <input 
                        v-model="form.nama_eskul" 
                        type="text" 
                        placeholder="Contoh: Futsal, Tari, Pramuka..." 
                        class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448] focus:ring-1 focus:ring-[#213448] outline-none transition"
                        required
                    />
                </div>

                <!-- Pembimbing (Multiple) -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-[#213448]">Daftar Pembimbing</label>
                    
                    <div class="space-y-2">
                        <!-- Loop untuk setiap slot pembimbing -->
                        <div v-for="(pembimbingId, index) in form.pembimbings" :key="index" class="flex gap-2">
                            <div class="relative w-full">
                                <select 
                                    v-model="form.pembimbings[index]" 
                                    class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448] focus:ring-1 focus:ring-[#213448] outline-none transition appearance-none bg-white"
                                    required
                                >
                                    <option value="" disabled>-- Pilih Pembimbing {{ index + 1 }} --</option>
                                    <!-- Menggunakan template v-for untuk melakukan filtering opsi -->
                                    <template v-for="p in pembimbings" :key="p.id_pembimbing">
                                        <option 
                                            :value="p.id_pembimbing" 
                                            v-if="isPembimbingAvailable(p.id_pembimbing, index)"
                                        >
                                            {{ p.nama_lengkap }}
                                        </option>
                                    </template>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>

                            <!-- Tombol Hapus (Merah) jika lebih dari 1 slot -->
                            <button 
                                v-if="form.pembimbings.length > 1"
                                type="button" 
                                @click="removePembimbing(index)"
                                class="p-2.5 text-red-500 hover:bg-red-50 rounded-lg border border-red-200 transition"
                                title="Hapus Pembimbing Ini"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Tambah Pembimbing -->
                    <button 
                        type="button" 
                        @click="addPembimbing"
                        class="mt-2 text-sm font-bold text-[#547792] hover:text-[#213448] flex items-center gap-1 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Pembimbing Lain
                    </button>
                </div>

                <!-- Deskripsi -->
                <div class="space-y-1">
                    <label class="block text-sm font-bold text-[#213448]">Deskripsi Singkat</label>
                    <textarea 
                        v-model="form.deskripsi" 
                        rows="3" 
                        placeholder="Jelaskan kegiatan eskul ini..." 
                        class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448] focus:ring-1 focus:ring-[#213448] outline-none transition resize-none"
                    ></textarea>
                </div>

                <!-- Jenjang Kelas -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-[#213448]">Min. Kelas</label>
                        <select v-model="form.jenjang_kelas_min" class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448]">
                            <option v-for="n in 6" :key="n" :value="String(n)">Kelas {{ n }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-[#213448]">Max. Kelas</label>
                        <select v-model="form.jenjang_kelas_max" class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448]">
                            <option v-for="n in 6" :key="n" :value="String(n)">Kelas {{ n }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[#94B4C1]/30">
                    <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm font-bold text-[#547792] hover:text-[#213448]">Batal</button>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-[#213448] px-6 py-2 text-sm font-bold text-[#EAE0CF] shadow hover:bg-[#547792] transition">
                        {{ eskulData ? 'Simpan Perubahan' : 'Tambah Eskul' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>