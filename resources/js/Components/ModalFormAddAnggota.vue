<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch,computed } from 'vue';

const props = defineProps({
    show: Boolean,
    idEskul: [Number, String],
    anggotaData: Object,
    // Tambahkan props baru untuk batasan
    minKelas: {
        type: [Number, String],
        default: 1
    },
    maxKelas: {
        type: [Number, String],
        default: 6
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    // Peserta
    nama_lengkap: '',
    tingkat_kelas: '1',
    jenis_kelamin: 'L',
    
    // Anggota
    id_eskul: '',
    tahun_ajaran: new Date().getFullYear() + '/' + (new Date().getFullYear() + 1),
    status_aktif: true, // Tambahan untuk edit status
});

const getCurrentTahunAjaran = () => {
    const now = new Date();
    const month = now.getMonth(); // 0 (Jan) - 11 (Des)
    const year = now.getFullYear();
    
    // Jika bulan 6 (Juli) atau lebih, berarti tahun ajaran baru dimulai tahun ini
    // Jika sebelum Juli, berarti masih tahun ajaran yang mulai tahun lalu
    const startYear = month >= 6 ? year : year - 1; 
    
    return `${startYear}/${startYear + 1}`;
};

const kelasOptions = computed(() => {
    const min = parseInt(props.minKelas) || 1;
    const max = parseInt(props.maxKelas) || 6;
    const options = [];
    for (let i = min; i <= max; i++) {
        options.push(String(i));
    }
    return options;
});

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        if (props.anggotaData) {
            // MODE EDIT
            const a = props.anggotaData;
            form.id_eskul = a.id_eskul;
            form.tahun_ajaran = a.tahun_ajaran;
            form.status_aktif = Boolean(a.status_aktif); // Pastikan boolean
            
            // Data Peserta (Relasi)
            if (a.peserta) {
                form.nama_lengkap = a.peserta.nama_lengkap;
                form.tingkat_kelas = a.peserta.tingkat_kelas;
                form.jenis_kelamin = a.peserta.jenis_kelamin;
            }
        } else {
            // MODE TAMBAH
            form.reset();
            form.id_eskul = props.idEskul;
            // Set default kelas ke nilai minimal yang diizinkan
            form.tingkat_kelas = String(props.minKelas); 
            form.jenis_kelamin = 'L';
            form.status_aktif = true;
            form.tahun_ajaran = getCurrentTahunAjaran();
        }
    }
});

const submit = () => {
    const url = props.anggotaData ? `/admin/anggota/${props.anggotaData.id_anggota}` : '/admin/anggota';
    const method = props.anggotaData ? 'put' : 'post';

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
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
        <div v-if="show" class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-[#213448]/80 backdrop-blur-sm p-4" @click.self="$emit('close')">
            
            <div class="relative w-full max-w-lg transform rounded-2xl bg-[#FFF] shadow-2xl transition-all border border-[#94B4C1]">
                
                <div class="flex items-center justify-between border-b border-[#94B4C1]/50 px-6 py-4">
                    <h3 class="text-xl font-bold text-[#213448]">
                        {{ anggotaData ? 'Edit Data Anggota' : 'Tambah Anggota Baru' }}
                    </h3>
                    <button @click="$emit('close')" class="rounded-lg p-1 text-[#547792] hover:bg-[#213448]/10 hover:text-[#213448]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-5">
                    
                    <!-- Data Peserta -->
                    <div class="space-y-4 rounded-xl bg-white/50 p-4 border border-[#94B4C1]/30">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-[#547792] mb-2 border-b border-[#94B4C1]/20 pb-1">Data Siswa</h4>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-[#213448]">Nama Lengkap</label>
                            <input type="text" v-model="form.nama_lengkap" class="w-full rounded-lg border border-[#94B4C1] bg-white p-3 text-[#213448] focus:border-[#213448] focus:ring-2 focus:ring-[#547792]/20">
                            <span v-if="form.errors.nama_lengkap" class="text-xs text-red-500">{{ form.errors.nama_lengkap }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-[#213448]">Kelas</label>
                                <select v-model="form.tingkat_kelas" class="w-full rounded-lg border border-[#94B4C1] bg-white p-3 text-[#213448] focus:border-[#213448] focus:ring-2 focus:ring-[#547792]/20">
                                    <option v-for="k in kelasOptions" :key="k" :value="k">Kelas {{ k }}</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-[#213448]">Jenis Kelamin</label>
                                <div class="flex gap-4 pt-3">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="form.jenis_kelamin" value="L" class="text-[#213448] focus:ring-[#213448]">
                                        <span class="text-sm text-[#213448]">Laki-laki</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" v-model="form.jenis_kelamin" value="P" class="text-[#213448] focus:ring-[#213448]">
                                        <span class="text-sm text-[#213448]">Perempuan</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Keanggotaan -->
                    <div class="space-y-4 rounded-xl bg-white/50 p-4 border border-[#94B4C1]/30">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-[#547792] mb-2 border-b border-[#94B4C1]/20 pb-1">Status Keanggotaan</h4>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-[#213448]">Tahun Ajaran</label>
                                <input type="text" v-model="form.tahun_ajaran" class="w-full rounded-lg border border-[#94B4C1] bg-white p-3 text-[#213448] focus:border-[#213448] focus:ring-2 focus:ring-[#547792]/20">
                            </div>

                            <!-- Status Aktif (Hanya muncul saat edit) -->
                            <div v-if="anggotaData" class="space-y-2">
                                <label class="block text-sm font-bold text-[#213448]">Status</label>
                                <select v-model="form.status_aktif" class="w-full rounded-lg border border-[#94B4C1] bg-white p-3 text-[#213448] focus:border-[#213448] focus:ring-2 focus:ring-[#547792]/20">
                                    <option :value="true">Aktif</option>
                                    <option :value="false">Non-Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#94B4C1]/50">
                        <button type="button" @click="$emit('close')" class="rounded-lg px-4 py-2 text-sm font-bold text-[#547792] hover:bg-[#213448]/5 transition">Batal</button>
                        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-lg bg-[#213448] px-6 py-2 text-sm font-bold text-[#EAE0CF] shadow-lg hover:bg-[#547792] transition">
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </Transition>
</template>