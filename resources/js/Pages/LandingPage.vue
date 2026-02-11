<script setup>
import { Head, Link } from '@inertiajs/vue3';
import GuestNavbar from '@/Components/GuestNavbar.vue';
import Footer from '@/Components/Footer.vue';
import { onMounted } from 'vue';

// Definisikan Props (Data ini harus dikirim dari Route '/' di web.php)
const props = defineProps({
    prestasiList: {
        type: Array,
        default: () => [
            // Mockup Data jika backend belum siap, nanti dihapus
            {
                id: 1,
                nama_lomba: 'Lomba Matematika Nasional',
                juara_ke: 'Juara 1',
                tingkat: 'Nasional',
                peserta: { nama_lengkap: 'Ahmad Dahlan' },
                tanggal_lomba: '2025-12-10',
                foto: null
            },
            {
                id: 2,
                nama_lomba: 'Pencak Silat Pelajar',
                juara_ke: 'Medali Emas',
                tingkat: 'Provinsi',
                peserta: { nama_lengkap: 'Siti Walidah' },
                tanggal_lomba: '2025-11-05',
                foto: null
            },
            {
                id: 3,
                nama_lomba: 'Pidato Bahasa Arab',
                juara_ke: 'Juara Harapan 1',
                tingkat: 'Kabupaten',
                peserta: { nama_lengkap: 'Fulan bin Fulan' },
                tanggal_lomba: '2026-01-15',
                foto: null
            }
        ]
    },
    totalEskul: { type: Number, default: 12 },
    totalSiswa: { type: Number, default: 350 },
    totalPrestasi: { type: Number, default: 45 }
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    });
};

console.log(props.prestasiList);
</script>

<template>
    <Head title="Selamat Datang" />

    <div class="min-h-screen bg-[#FDFBF7] font-sans text-[#213448]">
        
        <GuestNavbar />

        <!-- HERO SECTION -->
        <header id="beranda" class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden bg-[#213448]">
            <!-- Decorative Background Elements -->
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
                <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-[#547792] opacity-20 blur-3xl"></div>
                <div class="absolute top-1/2 -left-24 w-72 h-72 rounded-full bg-[#94B4C1] opacity-10 blur-3xl"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <span class="inline-block py-1 px-3 rounded-full bg-[#547792]/20 border border-[#547792]/30 text-[#94B4C1] text-xs font-bold uppercase tracking-widest mb-6 animate-fade-in-up">
                    Sistem Informasi Ekstrakurikuler
                </span>
                
                <h1 class="text-4xl md:text-6xl font-extrabold text-[#EAE0CF] tracking-tight mb-6 leading-tight animate-fade-in-up delay-100">
                    Kembangkan Bakat,<br> 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#94B4C1] to-white">Raih Prestasi Gemilang.</span>
                </h1>
                
                <p class="mt-4 text-lg md:text-xl text-[#94B4C1] max-w-2xl mx-auto mb-10 animate-fade-in-up delay-200">
                    Wadah digital terintegrasi untuk memantau perkembangan ekstrakurikuler, jadwal, absensi, dan capaian prestasi siswa SD Muhammadiyah Birrul Walidain.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 animate-fade-in-up delay-300">
                    <Link 
                        href="/login" 
                        class="w-full sm:w-auto px-8 py-3.5 bg-[#EAE0CF] hover:bg-white text-[#213448] font-bold rounded-full shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all flex items-center justify-center gap-2"
                    >
                        Masuk Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                    <a 
                        href="#prestasi" 
                        class="w-full sm:w-auto px-8 py-3.5 bg-transparent border border-[#94B4C1] text-[#EAE0CF] hover:bg-[#94B4C1]/10 font-bold rounded-full transition-all"
                    >
                        Lihat Prestasi
                    </a>
                </div>
            </div>
        </header>

        <!-- STATS SECTION -->
        <section class="relative -mt-10 z-20 px-4">
            <div class="mx-auto max-w-5xl">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Stat 1 -->
                    <div class="bg-white p-6 rounded-2xl shadow-xl border-b-4 border-[#213448] flex items-center gap-4 hover:-translate-y-1 transition-transform">
                        <div class="p-3 bg-blue-50 rounded-xl text-[#213448]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-3xl font-extrabold text-[#213448]">{{ props.totalEskul }}</p>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Ekstrakurikuler</p>
                        </div>
                    </div>
                    <!-- Stat 2 -->
                    <div class="bg-white p-6 rounded-2xl shadow-xl border-b-4 border-[#547792] flex items-center gap-4 hover:-translate-y-1 transition-transform">
                        <div class="p-3 bg-cyan-50 rounded-xl text-[#547792]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-3xl font-extrabold text-[#213448]">{{ props.totalSiswa }}</p>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Siswa Aktif</p>
                        </div>
                    </div>
                    <!-- Stat 3 -->
                    <div class="bg-white p-6 rounded-2xl shadow-xl border-b-4 border-yellow-500 flex items-center gap-4 hover:-translate-y-1 transition-transform">
                        <div class="p-3 bg-yellow-50 rounded-xl text-yellow-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-3xl font-extrabold text-[#213448]">{{ props.totalPrestasi }}</p>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Prestasi Diraih</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PRESTASI SECTION -->
        <section id="prestasi" class="py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-[#213448] mb-4">Galeri Prestasi Siswa</h2>
                    <p class="text-[#547792] max-w-2xl mx-auto">
                        Bukti nyata dedikasi dan kerja keras siswa-siswi SD Muhammadiyah Birrul Walidain dalam mengharumkan nama sekolah.
                    </p>
                    <div class="h-1 w-24 bg-[#213448] mx-auto mt-6 rounded-full"></div>
                </div>

                <!-- Grid Prestasi -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="item in props.prestasiList" 
                        :key="item.id" 
                        class="group bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 border border-gray-100 flex flex-col"
                    >
                        <!-- Image / Placeholder -->
                        <div class="h-48 bg-gray-200 relative overflow-hidden">
                            <div v-if="!item.foto_prestasi" class="absolute inset-0 flex items-center justify-center bg-[#213448]/5 text-[#94B4C1]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <img 
                                v-else 
                                :src="`/storage/${item.foto_prestasi}`" 
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
                                alt="Foto Prestasi"
                            >
                            
                            <!-- Badge Tingkat -->
                            <div class="absolute top-4 right-4">
                                <span 
                                    class="px-3 py-1 text-xs font-bold text-white rounded-full shadow-sm uppercase tracking-wide"
                                    :class="{
                                        'bg-purple-500': item.tingkat === 'Nasional',
                                        'bg-blue-500': item.tingkat === 'Provinsi',
                                        'bg-emerald-500': item.tingkat === 'Kabupaten',
                                        'bg-orange-500': item.tingkat === 'Kecamatan',
                                    }"
                                >
                                    {{ item.tingkat }}
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="mb-4">
                                <h3 class="text-lg font-bold text-[#213448] leading-tight mb-1 group-hover:text-[#547792] transition-colors">
                                    {{ item.nama_lomba }}
                                </h3>
                                <p class="text-sm font-semibold text-yellow-600 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    {{ formatDate(item.tanggal_lomba) }}
                                </p>
                            </div>

                            <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-400 font-medium uppercase">Siswa</span>
                                    <span class="text-sm font-bold text-[#547792]">{{ item.peserta?.nama_lengkap }}</span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-xs text-gray-400 font-medium uppercase">Capaian</span>
                                    <span class="text-sm font-bold text-[#213448]">{{ item.juara_ke }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="props.prestasiList.length === 0" class="text-center py-12">
                    <p class="text-gray-400 italic">Belum ada data prestasi yang ditampilkan.</p>
                </div>

                <div class="text-center mt-12">
                    <button class="text-[#547792] font-bold hover:text-[#213448] transition flex items-center justify-center gap-2 mx-auto">
                        Lihat Lebih Banyak
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </div>
        </section>

        <!-- CTA SECTION -->
        <section class="bg-[#547792] py-16 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 pattern-dots"></div>
            <div class="mx-auto max-w-4xl px-4 text-center relative z-10">
                <h2 class="text-3xl font-bold text-white mb-4">Bergabunglah Bersama Kami</h2>
                <p class="text-blue-100 text-lg mb-8">
                    Dukung potensi anak Anda dengan berbagai pilihan ekstrakurikuler yang mendidik dan menyenangkan.
                </p>
                <Link href="/login" class="inline-block bg-[#EAE0CF] text-[#213448] px-8 py-3 rounded-full font-bold shadow-lg hover:bg-white transition-all transform hover:scale-105">
                    Login Sistem Eskul
                </Link>
            </div>
        </section>

        <Footer />
    </div>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    opacity: 0;
    transform: translateY(20px);
}

.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Pattern Dots CSS (Optional) */
.pattern-dots {
    background-image: radial-gradient(#ffffff 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>