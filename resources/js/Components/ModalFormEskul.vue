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

const form = useForm({
    nama_eskul: '',
    id_pembimbing: '',
    deskripsi: '',
    jenjang_kelas_min: '1',
    jenjang_kelas_max: '6',
});

// Computed untuk cek mode edit
const isEditMode = computed(() => !!props.eskulData);

// Watcher dengan immediate: true agar form terisi saat modal muncul (karena v-if di parent)
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        if (props.eskulData) {
            // MODE EDIT: Isi data otomatis agar tidak bingung
            form.nama_eskul = props.eskulData.nama_eskul || '';
            form.id_pembimbing = props.eskulData.id_pembimbing || '';
            form.deskripsi = props.eskulData.deskripsi || '';
            form.jenjang_kelas_min = String(props.eskulData.jenjang_kelas_min || '1');
            form.jenjang_kelas_max = String(props.eskulData.jenjang_kelas_max || '6');
        } else {
            // MODE TAMBAH: Reset form
            form.reset();
            form.jenjang_kelas_min = '1';
            form.jenjang_kelas_max = '6';
        }
    }
}, { immediate: true }); // <--- PENTING: immediate true agar jalan saat mount

const submit = () => {
    const url = props.eskulData ? `/admin/eskul/${props.eskulData.id_eskul}` : '/admin/eskul';
    const method = props.eskulData ? 'put' : 'post';

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
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border-t-4 border-[#213448] transform transition-all scale-100">
            
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

                <!-- Pembimbing (Locked saat Edit) -->
                <div class="space-y-1">
                    <label class="block text-sm font-bold text-[#213448]">Pembimbing</label>
                    <div class="relative">
                        <select 
                            v-model="form.id_pembimbing" 
                            :disabled="isEditMode"
                            class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448] focus:ring-1 focus:ring-[#213448] outline-none transition appearance-none disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                            required
                        >
                            <option value="" disabled>-- Pilih Pembimbing --</option>
                            <option v-for="p in pembimbings" :key="p.id_pembimbing" :value="p.id_pembimbing">
                                {{ p.nama_lengkap }}
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                        </div>
                    </div>
                    <p v-if="isEditMode" class="text-xs text-[#547792] italic mt-1">
                        * Pembimbing tidak dapat diubah saat edit.
                    </p>
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