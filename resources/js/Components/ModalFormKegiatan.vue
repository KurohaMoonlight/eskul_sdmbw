<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    show: Boolean,
    idEskul: [Number, String],
    selectedDate: [Date, String], // Tanggal yang diklik di kalender
    kegiatanData: Object, // Untuk edit (opsional)
});

const emit = defineEmits(['close']);

const form = useForm({
    id_eskul: '',
    tanggal: '',
    materi_kegiatan: '',
    catatan_pembimbing: '',
});

// Helper untuk format Date Object ke YYYY-MM-DD (format input date HTML)
const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const month = '' + (d.getMonth() + 1);
    const day = '' + d.getDate();
    const year = d.getFullYear();

    if (month.length < 2) 
        return [year, '0' + month, day].join('-');
        
    return [year, month, day].join('-');
};

watch(() => props.show, (isOpen) => {
    if (isOpen) {
        if (props.kegiatanData) {
            // Mode Edit
            form.id_eskul = props.kegiatanData.id_eskul;
            form.tanggal = props.kegiatanData.tanggal;
            form.materi_kegiatan = props.kegiatanData.materi_kegiatan;
            form.catatan_pembimbing = props.kegiatanData.catatan_pembimbing;
        } else {
            // Mode Tambah
            form.reset();
            form.id_eskul = props.idEskul;
            // Isi tanggal otomatis dari tanggal yang diklik di kalender
            form.tanggal = props.selectedDate ? formatDate(props.selectedDate) : '';
        }
    }
});

const submit = () => {
    // Pastikan ID Eskul terisi
    form.id_eskul = props.idEskul;

    const url = props.kegiatanData ? `/admin/kegiatan/${props.kegiatanData.id_kegiatan}` : '/admin/kegiatan';
    const method = props.kegiatanData ? 'put' : 'post';

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
                        {{ kegiatanData ? 'Edit Kegiatan' : 'Tambah Kegiatan' }}
                    </h3>
                    <button @click="$emit('close')" class="rounded-lg p-1 text-[#547792] hover:bg-[#213448]/10 hover:text-[#213448]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-5">
                    
                    <div class="space-y-4">
                        <!-- Tanggal Kegiatan -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-[#213448]">Tanggal Kegiatan</label>
                            <input 
                                type="date" 
                                v-model="form.tanggal" 
                                class="w-full rounded-lg border border-[#94B4C1] bg-white p-3 text-[#213448] focus:border-[#213448] focus:ring-2 focus:ring-[#547792]/20"
                            >
                            <span v-if="form.errors.tanggal" class="text-xs text-red-500">{{ form.errors.tanggal }}</span>
                        </div>

                        <!-- Materi Kegiatan -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-[#213448]">Materi Kegiatan</label>
                            <textarea 
                                v-model="form.materi_kegiatan" 
                                rows="3"
                                placeholder="Jelaskan materi yang disampaikan..."
                                class="w-full rounded-lg border border-[#94B4C1] bg-white p-3 text-[#213448] focus:border-[#213448] focus:ring-2 focus:ring-[#547792]/20"
                            ></textarea>
                            <span v-if="form.errors.materi_kegiatan" class="text-xs text-red-500">{{ form.errors.materi_kegiatan }}</span>
                        </div>

                        <!-- Catatan Pembimbing -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-[#213448]">Catatan Pembimbing (Opsional)</label>
                            <textarea 
                                v-model="form.catatan_pembimbing" 
                                rows="2"
                                placeholder="Catatan tambahan..."
                                class="w-full rounded-lg border border-[#94B4C1] bg-white p-3 text-[#213448] focus:border-[#213448] focus:ring-2 focus:ring-[#547792]/20"
                            ></textarea>
                            <span v-if="form.errors.catatan_pembimbing" class="text-xs text-red-500">{{ form.errors.catatan_pembimbing }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#94B4C1]/50">
                        <button type="button" @click="$emit('close')" class="rounded-lg px-4 py-2 text-sm font-bold text-[#547792] hover:bg-[#213448]/5 transition">Batal</button>
                        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 rounded-lg bg-[#213448] px-6 py-2 text-sm font-bold text-[#EAE0CF] shadow-lg hover:bg-[#547792] transition">
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Kegiatan' }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </Transition>
</template>