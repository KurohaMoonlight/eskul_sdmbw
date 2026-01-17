<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { watch, ref } from 'vue';
import Swal from 'sweetalert2'; // Pastikan sudah install: npm install sweetalert2

const props = defineProps({
    show: Boolean,                  // Trigger tampil/sembunyi
    pembimbingData: Object,         // Data edit (null jika tambah baru)
});

const emit = defineEmits(['close']);

// State untuk fitur password
const showPassword = ref(false);

const form = useForm({
    nama_lengkap: '',
    username: '',
    password: '',
});

// Watcher: Reset/Isi form saat modal dibuka
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        showPassword.value = false; // Reset view password
        if (props.pembimbingData) {
            // Mode Edit
            form.nama_lengkap = props.pembimbingData.nama_lengkap;
            form.username = props.pembimbingData.username;
            form.password = ''; // Kosongkan password saat edit
        } else {
            // Mode Tambah
            form.reset();
            generatePassword(); // Auto generate password
        }
    }
});

// Generator Password Mudah Diingat
const generatePassword = () => {
    const randomNum = Math.floor(Math.random() * 900) + 100; // 100 - 999
    form.password = `SdmBw${randomNum}`;
    showPassword.value = true;
};

const submit = () => {
    const url = props.pembimbingData ? `/admin/pembimbing/${props.pembimbingData.id_pembimbing}` : '/admin/pembimbing';
    const method = props.pembimbingData ? 'put' : 'post';

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
            // Notifikasi Sukses
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data pembimbing berhasil disimpan.',
                timer: 2000,
                showConfirmButton: false,
                background: '#fff',
                color: '#213448'
            });
        },
    });
};

// Fungsi Delete dengan SweetAlert2
const deletePembimbing = () => {
    Swal.fire({
        title: 'Yakin hapus pembimbing ini?',
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33', // Merah untuk bahaya
        cancelButtonColor: '#547792', // Biru untuk batal
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#EAE0CF',
        color: '#213448',
        iconColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/admin/pembimbing/${props.pembimbingData.id_pembimbing}`, {
                preserveScroll: true,
                onSuccess: () => {
                    emit('close');
                    Swal.fire({
                        title: 'Terhapus!',
                        text: 'Data pembimbing telah dihapus.',
                        icon: 'success',
                        background: '#EAE0CF',
                        color: '#213448',
                        confirmButtonColor: '#547792'
                    });
                },
            });
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
        <!-- Wrapper Modal (Overlay Penuh Layar) -->
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-[#213448]/80 backdrop-blur-sm p-4 md:p-6" @click.self="$emit('close')">
            
            <!-- Card Modal -->
            <div class="relative w-full max-w-xl transform rounded-2xl bg-[#EAE0CF] shadow-2xl transition-all border border-[#94B4C1]">
                
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-[#94B4C1]/50 px-6 py-4">
                    <h3 class="text-xl font-bold text-[#213448]">
                        {{ pembimbingData ? 'Edit Data Pembimbing' : 'Tambah Pembimbing Baru' }}
                    </h3>
                    <button @click="$emit('close')" class="rounded-lg p-1 text-[#547792] hover:bg-[#213448]/10 hover:text-[#213448] transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form Body -->
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    
                    <!-- Nama Lengkap -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-[#213448]">Nama Lengkap</label>
                        <input 
                            type="text" 
                            v-model="form.nama_lengkap"
                            class="w-full rounded-lg border border-[#94B4C1] bg-white p-3 text-[#213448] placeholder-[#94B4C1] focus:border-[#213448] focus:outline-none focus:ring-2 focus:ring-[#547792]/20 transition-all"
                            placeholder="Contoh: Ahmad Dahlan, S.Pd"
                        >
                        <span v-if="form.errors.nama_lengkap" class="text-xs text-red-500">{{ form.errors.nama_lengkap }}</span>
                    </div>

                    <!-- Username -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-[#213448]">Username</label>
                        <input 
                            type="text" 
                            v-model="form.username"
                            class="w-full rounded-lg border border-[#94B4C1] bg-white p-3 text-[#213448] placeholder-[#94B4C1] focus:border-[#213448] focus:outline-none focus:ring-2 focus:ring-[#547792]/20 transition-all"
                            placeholder="Username untuk login"
                        >
                        <span v-if="form.errors.username" class="text-xs text-red-500">{{ form.errors.username }}</span>
                    </div>

                    <!-- Password Group -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-[#213448]">
                            Password 
                            <span v-if="pembimbingData" class="text-xs font-normal text-[#547792] ml-1">(Isi hanya jika ingin mengubah)</span>
                        </label>
                        
                        <div class="flex gap-2">
                            <!-- Input Password -->
                            <div class="relative flex-1">
                                <input 
                                    :type="showPassword ? 'text' : 'password'" 
                                    v-model="form.password"
                                    class="w-full rounded-lg border border-[#94B4C1] bg-white p-3 pr-10 text-[#213448] placeholder-[#94B4C1] focus:border-[#213448] focus:outline-none focus:ring-2 focus:ring-[#547792]/20 transition-all"
                                    placeholder="••••••••"
                                >
                                <!-- Toggle Visibility Icon -->
                                <button 
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-[#547792] hover:text-[#213448] transition-colors focus:outline-none"
                                    tabindex="-1"
                                >
                                    <svg v-if="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Tombol Generate -->
                            <button 
                                type="button"
                                @click="generatePassword"
                                class="shrink-0 rounded-lg bg-[#547792] px-3 py-2 text-sm font-bold text-white shadow-md transition hover:bg-[#213448] active:scale-95"
                                title="Generate Password Mudah Diingat"
                            >
                                <svg class="h-5 w-5 md:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="hidden md:inline">Generate</span>
                            </button>
                        </div>
                        <span v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</span>
                    </div>

                    <!-- Footer / Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#94B4C1]/50">
                        
                        <!-- Tombol Hapus (Kiri) - Hanya muncul saat mode Edit -->
                        <button 
                            v-if="pembimbingData"
                            type="button" 
                            @click="deletePembimbing"
                            class="mr-auto rounded-lg px-4 py-2 text-sm font-bold text-red-500 transition hover:bg-red-50 hover:text-red-700"
                        >
                            Hapus
                        </button>

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
                            class="flex items-center gap-2 rounded-lg bg-[#213448] px-6 py-2 text-sm font-bold text-[#EAE0CF] shadow-lg transition hover:bg-[#547792] hover:shadow-xl disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-[#EAE0CF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </Transition>
</template>