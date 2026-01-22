<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    show: Boolean,
    idEskul: [Number, String],
    prestasiData: Object, // Null jika mode tambah
    anggota: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    id_eskul: '',
    // id_peserta diganti dengan array untuk UI, tapi nanti dikirim sebagai string/array ke backend
    // Kita gunakan field sementara 'anggota_tim' untuk menampung array ID
    anggota_tim: [''], 
    nama_lomba: '',
    tingkat: 'Kabupaten',
    juara_ke: '',
    tanggal_lomba: '',
    foto_prestasi: null, 
    // Field 'id_peserta' asli (untuk kompatibilitas backend jika backend minta 1 kolom string)
    id_peserta: '' 
});

// State untuk proses konversi & upload
const isProcessing = ref(false);
const progressPercent = ref(0);
const previewImage = ref(null);

// Reset form saat modal dibuka
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        progressPercent.value = 0;
        isProcessing.value = false;
        previewImage.value = null;

        if (props.prestasiData) {
            // Mode Edit
            const p = props.prestasiData;
            form.id_eskul = p.id_eskul;
            form.nama_lomba = p.nama_lomba;
            form.tingkat = p.tingkat;
            form.juara_ke = p.juara_ke;
            form.tanggal_lomba = p.tanggal_lomba;
            form.foto_prestasi = null;
            
            // Konversi string 'id1,id2' menjadi array ['id1', 'id2'] untuk ditampilkan di input
            // Asumsi: id_peserta di DB menyimpan string ID yang dipisah koma jika tim, 
            // atau single ID jika individu.
            if (p.id_peserta) {
                // Pastikan id_peserta diperlakukan sebagai string sebelum split
                form.anggota_tim = String(p.id_peserta).split(',');
            } else {
                form.anggota_tim = [''];
            }

            if (p.foto_prestasi) {
                previewImage.value = `/storage/${p.foto_prestasi}`;
            }
        } else {
            // Mode Tambah
            form.reset();
            form.id_eskul = props.idEskul;
            form.tingkat = 'Kabupaten';
            form.anggota_tim = ['']; // Default 1 input kosong
        }
    }
});

// --- LOGIKA DINAMIS ANGGOTA TIM ---

const addAnggotaInput = () => {
    form.anggota_tim.push(''); // Tambah slot kosong
};

const removeAnggotaInput = (index) => {
    form.anggota_tim.splice(index, 1);
};

// ----------------------------------

// Fungsi Konversi Gambar ke WebP (Client Side)
const convertToWebP = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                
                canvas.toBlob((blob) => {
                    if (blob) {
                        const webpFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".webp", {
                            type: "image/webp",
                            lastModified: Date.now()
                        });
                        resolve(webpFile);
                    } else {
                        reject(new Error('Konversi gagal'));
                    }
                }, 'image/webp', 0.8);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        previewImage.value = URL.createObjectURL(file);
        form.foto_prestasi = file; 
    }
};

const submit = async () => {
    isProcessing.value = true;
    progressPercent.value = 10; 

    try {
        // 1. Konversi Array Anggota ke String (Dipisah Koma)
        // Filter nilai kosong agar tidak ada ID kosong yang terkirim (misal: "1,,3")
        const validMembers = form.anggota_tim.filter(id => id !== '' && id !== null);
        
        // Simpan ke field id_peserta yang akan dikirim ke backend
        form.id_peserta = validMembers.join(','); 

        // 2. Konversi Gambar (Jika ada file baru)
        if (form.foto_prestasi instanceof File) {
            const interval = setInterval(() => {
                if (progressPercent.value < 80) progressPercent.value += 5;
            }, 100);

            try {
                const webpFile = await convertToWebP(form.foto_prestasi);
                form.foto_prestasi = webpFile;
                clearInterval(interval);
                progressPercent.value = 90; 
            } catch (error) {
                clearInterval(interval);
                console.error("Gagal konversi WebP", error);
                Swal.fire("Error", "Gagal memproses gambar.", "error");
                isProcessing.value = false;
                return;
            }
        } else {
            progressPercent.value = 50;
        }

        // 3. Kirim ke Server
        form.id_eskul = props.idEskul;
        const url = props.prestasiData ? `/admin/prestasi/${props.prestasiData.id_prestasi}` : '/admin/prestasi';
        const method = 'post'; 
        if (props.prestasiData) form.transform((data) => ({ ...data, _method: 'put' }));

        form.submit(method, url, {
            forceFormData: true,
            onProgress: (progress) => {
                if (progress.percentage) {
                    progressPercent.value = 90 + Math.round(progress.percentage * 0.1);
                }
            },
            onSuccess: () => {
                progressPercent.value = 100;
                setTimeout(() => {
                    emit('close');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data prestasi berhasil disimpan.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 600);
            },
            onError: () => {
                isProcessing.value = false;
                progressPercent.value = 0;
            },
            onFinish: () => {
                if (form.hasErrors) {
                    isProcessing.value = false;
                }
            }
        });

    } catch (e) {
        isProcessing.value = false;
        alert("Terjadi kesalahan sistem.");
    }
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <div v-if="show" class="fixed inset-0 z-[80] flex items-center justify-center overflow-y-auto bg-[#213448]/80 backdrop-blur-sm p-4" @click.self="$emit('close')">
            
            <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-gray-100 overflow-hidden transform transition-all">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-[#213448] to-[#3a506b] px-6 py-5 flex justify-between items-center shadow-md">
                    <h3 class="text-xl font-bold text-[#EAE0CF] tracking-wide flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        {{ prestasiData ? 'Edit Data Prestasi' : 'Catat Prestasi Baru' }}
                    </h3>
                    <button @click="$emit('close')" class="text-white/70 hover:text-white transition-colors bg-white/10 p-1 rounded-lg hover:bg-white/20">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-5 bg-gray-50/30 max-h-[80vh] overflow-y-auto">
                    
                    <!-- Nama Lomba -->
                    <div class="space-y-1.5">
                        <label class="block text-sm font-bold text-gray-700">Nama Lomba / Kejuaraan <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            v-model="form.nama_lomba" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#547792] focus:ring-2 focus:ring-[#547792]/20 transition-all placeholder-gray-400 text-sm" 
                            placeholder="Contoh: Lomba Coding Nasional 2025"
                        >
                        <p v-if="form.errors.nama_lomba" class="text-red-500 text-xs font-medium flex items-center gap-1 mt-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ form.errors.nama_lomba }}
                        </p>
                    </div>

                    <!-- Grid: Tingkat & Juara -->
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="block text-sm font-bold text-gray-700">Tingkat</label>
                            <div class="relative">
                                <select v-model="form.tingkat" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#547792] focus:ring-2 focus:ring-[#547792]/20 transition-all text-sm appearance-none bg-white">
                                    <option value="Kecamatan">Kecamatan</option>
                                    <option value="Kabupaten">Kabupaten</option>
                                    <option value="Provinsi">Provinsi</option>
                                    <option value="Nasional">Nasional</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-bold text-gray-700">Perolehan Juara <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                v-model="form.juara_ke" 
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#547792] focus:ring-2 focus:ring-[#547792]/20 transition-all placeholder-gray-400 text-sm" 
                                placeholder="Cth: 1, Harapan 2"
                            >
                            <p v-if="form.errors.juara_ke" class="text-red-500 text-xs font-medium">{{ form.errors.juara_ke }}</p>
                        </div>
                    </div>

                    <!-- Tanggal -->
                    <div class="space-y-1.5">
                        <label class="block text-sm font-bold text-gray-700">Tanggal Pelaksanaan</label>
                        <input 
                            type="date" 
                            v-model="form.tanggal_lomba" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#547792] focus:ring-2 focus:ring-[#547792]/20 transition-all text-sm"
                        >
                        <p v-if="form.errors.tanggal_lomba" class="text-red-500 text-xs font-medium">{{ form.errors.tanggal_lomba }}</p>
                    </div>

                    <!-- Input Anggota Tim / Individu -->
                    <div class="space-y-3 p-4 bg-gray-50 border border-gray-200 rounded-xl">
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-bold text-gray-700">Peraih / Anggota Tim</label>
                            <button 
                                type="button" 
                                @click="addAnggotaInput" 
                                class="text-xs bg-white border border-[#547792] text-[#547792] px-2 py-1 rounded hover:bg-[#547792] hover:text-white transition flex items-center gap-1"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                Tambah Anggota
                            </button>
                        </div>
                        
                        <!-- Loop Input Anggota -->
                        <div v-for="(memberId, index) in form.anggota_tim" :key="index" class="flex gap-2 items-center">
                            <div class="relative flex-1">
                                <select 
                                    v-model="form.anggota_tim[index]" 
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-[#547792] focus:ring-2 focus:ring-[#547792]/20 transition-all text-sm appearance-none bg-white"
                                >
                                    <option value="" disabled>-- Pilih Siswa --</option>
                                    <option v-for="a in anggota" :key="a.id_peserta" :value="String(a.peserta.id_peserta)">
                                        {{ a.peserta.nama_lengkap }} (Kelas {{ a.peserta.tingkat_kelas }})
                                    </option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                            
                            <!-- Tombol Hapus (Muncul jika lebih dari 1 input) -->
                            <button 
                                v-if="form.anggota_tim.length > 1" 
                                type="button" 
                                @click="removeAnggotaInput(index)"
                                class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                title="Hapus Anggota"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-400 italic">
                            * Tambahkan baris baru untuk prestasi Tim/Beregu. Kosongkan jika belum ditentukan.
                        </p>
                    </div>

                    <!-- Upload Foto -->
                    <div class="bg-white p-4 rounded-xl border border-dashed border-gray-300 hover:border-[#547792] transition-colors group">
                        <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#547792]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Dokumentasi / Foto Piala
                        </label>
                        <div class="flex items-start gap-4">
                            <!-- Preview Box -->
                            <div class="w-24 h-24 rounded-lg overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center shrink-0 shadow-sm relative">
                                <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover">
                                <span v-else class="text-xs text-gray-400 text-center px-2">Preview Foto</span>
                                
                                <!-- Indicator Convert -->
                                <div v-if="isProcessing && progressPercent < 90" class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                    <svg class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Input File -->
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    @change="handleFileChange" 
                                    accept="image/*" 
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#547792]/10 file:text-[#547792] hover:file:bg-[#547792]/20 transition-all cursor-pointer"
                                >
                                <p class="text-[10px] text-gray-400 mt-2 leading-relaxed">
                                    Format: JPG/PNG. Otomatis dikonversi ke <strong>WebP</strong> untuk performa maksimal. Max ukuran upload: 2MB.
                                </p>
                                <p v-if="form.errors.foto_prestasi" class="text-red-500 text-xs font-medium mt-1">{{ form.errors.foto_prestasi }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL PROSES (CUSTOM BATTERY ANIMATION) -->
                    <div class="pt-6 border-t border-gray-200/60">
                        <div v-if="isProcessing" class="relative w-full h-12 bg-gray-100 rounded-xl overflow-hidden border border-gray-300 shadow-inner">
                            <!-- Battery Fluid -->
                            <div 
                                class="absolute top-0 left-0 h-full bg-gradient-to-r from-[#547792] to-[#2a9d8f] transition-all duration-300 ease-out flex items-center justify-end pr-2"
                                :style="{ width: progressPercent + '%' }"
                            >
                                <div class="h-full w-2 bg-white/20 blur-sm transform skew-x-12"></div>
                            </div>
                            
                            <!-- Text Percentage (Centered) -->
                            <div class="absolute inset-0 flex items-center justify-center z-10 gap-2">
                                <span class="text-sm font-bold tracking-wider" :class="progressPercent > 50 ? 'text-white drop-shadow-md' : 'text-[#213448]'">
                                    {{ progressPercent < 100 ? `MEMPROSES... ${progressPercent}%` : 'SELESAI!' }}
                                </span>
                            </div>

                            <!-- Battery Bubbles Effect -->
                            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIvPjwvc3ZnPg==')] opacity-30 animate-pulse"></div>
                        </div>

                        <div v-else class="flex gap-3 w-full justify-end">
                            <button 
                                type="button" 
                                @click="$emit('close')" 
                                class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50 font-bold text-sm transition shadow-sm"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="px-8 py-2.5 rounded-xl bg-[#213448] text-[#EAE0CF] hover:bg-[#547792] hover:text-white font-bold text-sm shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:translate-y-0"
                            >
                                Simpan Data
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </Transition>
</template>