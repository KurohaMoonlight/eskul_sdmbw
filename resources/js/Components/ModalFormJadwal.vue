<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    jadwalData: Object,      // Data edit (null jika tambah baru)
    idEskul: [Number, String], // ID Eskul (Wajib untuk tambah baru)
    minLimit: { // Batas Bawah dari Eskul (misal: 3)
        type: [Number, String],
        default: '1'
    },
    maxLimit: { // Batas Atas dari Eskul (misal: 6)
        type: [Number, String],
        default: '6'
    }
});

const emit = defineEmits(['close']);

// Daftar Hari
const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

const form = useForm({
    id_eskul: '',
    hari: [], // Default string, bukan array
    jam_mulai: '',
    jam_selesai: '',
    lokasi: '', // Ganti lokasi jadi tempat sesuai DB
    kelas_min: '',
    kelas_max: '',
});

// Computed: Generate Opsi Kelas Sesuai Range Eskul
const classOptions = computed(() => {
    const start = parseInt(props.minLimit) || 1;
    const end = parseInt(props.maxLimit) || 6;
    const options = [];
    
    // Generate angka dari min sampai max
    for (let i = start; i <= end; i++) {
        options.push(String(i));
    }
    return options;
});

// Watcher: Reset/Isi form saat modal dibuka
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        if (props.jadwalData) {
            // MODE EDIT
            form.id_eskul = props.jadwalData.id_eskul;
            form.hari = props.jadwalData.hari;
            form.jam_mulai = props.jadwalData.jam_mulai;
            form.jam_selesai = props.jadwalData.jam_selesai;
            form.lokasi = props.jadwalData.lokasi;
            
            // Validasi: Jika data lama diluar range baru, sesuaikan ke min/max range
            const currentMin = String(props.jadwalData.kelas_min);
            const currentMax = String(props.jadwalData.kelas_max);
            
            form.kelas_min = classOptions.value.includes(currentMin) ? currentMin : String(props.minLimit);
            form.kelas_max = classOptions.value.includes(currentMax) ? currentMax : String(props.maxLimit);
        } else {
            // MODE TAMBAH
            form.reset();
            form.id_eskul = props.idEskul;
            form.hari = 'Senin';
            // Default range kelas mengikuti batasan eskul
            form.kelas_min = String(props.minLimit);
            form.kelas_max = String(props.maxLimit);
        }
    }
});

const submit = () => {
    // Validasi logic jam
    if (form.jam_selesai <= form.jam_mulai) {
        alert("Jam selesai harus lebih akhir dari jam mulai.");
        return;
    }

    // Validasi logic kelas
    if (parseInt(form.kelas_max) < parseInt(form.kelas_min)) {
        alert("Kelas maksimal tidak boleh lebih kecil dari kelas minimal.");
        return;
    }

    const url = props.jadwalData ? `/admin/jadwal/${props.jadwalData.id_jadwal}` : '/admin/jadwal';
    const method = props.jadwalData ? 'put' : 'post';

    // PERBAIKAN: Gunakan transform untuk mengubah string menjadi array khusus saat pengiriman
    form.transform((data) => ({
        ...data,
        hari: [data.hari] // Bungkus string 'Senin' menjadi ['Senin'] agar diterima Controller
    }))[method](url, {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-fade-in">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border-t-4 border-[#213448] transform transition-all scale-100">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-extrabold text-[#213448]">
                    {{ jadwalData ? 'Edit Jadwal' : 'Tambah Jadwal Baru' }}
                </h3>
                <button @click="$emit('close')" class="text-gray-400 hover:text-red-500 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                
                <!-- Hari -->
                <div class="space-y-1">
                    <label class="block text-sm font-bold text-[#213448]">Hari Kegiatan</label>
                    <select 
                        v-model="form.hari" 
                        class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448] focus:ring-1 focus:ring-[#213448] outline-none transition bg-white"
                        required
                    >
                        <option v-for="day in days" :key="day" :value="day">{{ day }}</option>
                    </select>
                </div>

                <!-- Jam Mulai & Selesai -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-[#213448]">Jam Mulai</label>
                        <input 
                            v-model="form.jam_mulai" 
                            type="time" 
                            class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448] focus:ring-1 focus:ring-[#213448] outline-none transition"
                            required
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-[#213448]">Jam Selesai</label>
                        <input 
                            v-model="form.jam_selesai" 
                            type="time" 
                            class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448] focus:ring-1 focus:ring-[#213448] outline-none transition"
                            required
                        />
                    </div>
                </div>

                <!-- Tempat / Lokasi -->
                <div class="space-y-1">
                    <label class="block text-sm font-bold text-[#213448]">Tempat / Lokasi</label>
                    <input 
                        v-model="form.lokasi" 
                        type="text" 
                        placeholder="Contoh: Lapangan A, Ruang Musik..." 
                        class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448] focus:ring-1 focus:ring-[#213448] outline-none transition"
                        required
                    />
                </div>

                <!-- Range Kelas -->
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <div class="col-span-2 text-xs font-bold text-[#547792] mb-1">
                        * Range kelas dibatasi sesuai jenjang eskul (Kelas {{ minLimit }} - {{ maxLimit }})
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-[#213448]">Min. Kelas</label>
                        <select v-model="form.kelas_min" class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448]">
                            <!-- Loop Opsi Dinamis -->
                            <option v-for="cls in classOptions" :key="cls" :value="cls">Kelas {{ cls }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-[#213448]">Max. Kelas</label>
                        <select v-model="form.kelas_max" class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448]">
                            <!-- Loop Opsi Dinamis -->
                            <option v-for="cls in classOptions" :key="cls" :value="cls">Kelas {{ cls }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[#94B4C1]/30">
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
                        {{ jadwalData ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
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