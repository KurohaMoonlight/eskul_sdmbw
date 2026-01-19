<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    show: Boolean,
    pembimbings: Array,
    eskulData: Object, // Bisa null jika mode tambah
});

const emit = defineEmits(['close']);

const form = useForm({
    nama_eskul: '',
    id_pembimbing: '',
    deskripsi: '',
    jenjang_kelas_min: '1',
    jenjang_kelas_max: '6',
});

// Watcher untuk mengisi form saat modal dibuka
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        if (props.eskulData) {
            // MODE EDIT: Isi data dari props
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
});

const submit = () => {
    const url = props.eskulData ? `/admin/eskul/${props.eskulData.id_eskul}` : '/admin/eskul';
    const method = props.eskulData ? 'put' : 'post';

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
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center bg-[#213448]/80 backdrop-blur-sm p-4" @click.self="$emit('close')">
            
            <div class="relative w-full max-w-xl transform rounded-2xl bg-[#FFF] shadow-2xl transition-all border border-[#94B4C1]">
                
                <div class="flex items-center justify-between border-b border-[#94B4C1]/50 px-6 py-4">
                    <h3 class="text-xl font-bold text-[#213448]">
                        {{ eskulData ? 'Edit Info Eskul' : 'Tambah Eskul Baru' }}
                    </h3>
                    <button @click="$emit('close')" class="rounded-lg p-1 text-[#547792] hover:bg-[#213448]/10 hover:text-[#213448]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <!-- Nama Eskul -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-[#213448]">Nama Ekstrakurikuler</label>
                        <input type="text" v-model="form.nama_eskul" class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448] focus:ring-1 focus:ring-[#213448] outline-none transition" placeholder="Seni Tari, Futsal, dsb.">
                        <p v-if="form.errors.nama_eskul" class="text-xs text-red-500">{{ form.errors.nama_eskul }}</p>
                    </div>

                    <!-- Pembimbing -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-[#213448]">Pembimbing</label>
                        <select v-model="form.id_pembimbing" class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448] focus:ring-1 focus:ring-[#213448] outline-none">
                            <option value="">Pilih Pembimbing</option>
                            <option v-for="p in pembimbings" :key="p.id_pembimbing" :value="p.id_pembimbing">
                                {{ p.nama_lengkap }}
                            </option>
                        </select>
                        <p v-if="form.errors.id_pembimbing" class="text-xs text-red-500">{{ form.errors.id_pembimbing }}</p>
                    </div>

                    <!-- Deskripsi -->
                    <div class="space-y-1">
                        <label class="block text-sm font-bold text-[#213448]">Deskripsi</label>
                        <textarea v-model="form.deskripsi" rows="3" class="w-full rounded-lg border border-[#94B4C1] p-2.5 focus:border-[#213448] focus:ring-1 focus:ring-[#213448] outline-none transition" placeholder="Penjelasan singkat mengenai eskul..."></textarea>
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
                            {{ eskulData ? 'Update Info' : 'Simpan Eskul' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>