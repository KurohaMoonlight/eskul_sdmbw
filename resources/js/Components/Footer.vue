<script setup>
// Mengambil tahun saat ini secara otomatis
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="w-full bg-[#213448] py-12 text-[#EAE0CF] border-t border-[#547792]/30">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                
                <!-- Bagian 1: Branding -->
                <div class="flex flex-col items-center md:items-start">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-xl font-bold tracking-tight">SD Muhammadiyah Birrul Walidain</span>
                    </div>
                    <p class="text-sm text-[#94B4C1] text-center md:text-left leading-relaxed">
                        Sistem Informasi Ekstrakurikuler Birrul Walidain Kudus. Mewujudkan generasi yang unggul dalam Imtaq dan Iptek.
                    </p>
                </div>

                <!-- Bagian 2: Kontak Kami (BARU) -->
                <div class="flex flex-col items-center md:items-start">
                    <h4 class="text-sm font-bold uppercase tracking-widest text-[#547792] mb-4">Kontak Kami</h4>
                    <ul class="space-y-3 text-sm text-[#94B4C1]">
                        <li class="flex items-start gap-3 justify-center md:justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-[#EAE0CF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-center md:text-left">Jl. HOS Cokroaminoto, Ds. Mlatinorowito, Gg. 10, RT 03 RW 09, Kab. Kudus, Provinsi Jawa Tengah.</span>
                        </li>
                        <li class="flex items-center gap-3 justify-center md:justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-[#EAE0CF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>(0812) 48076886 / (0291) 4248302</span>
                        </li>
                        <li class="flex items-center gap-3 justify-center md:justify-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-[#EAE0CF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <a href="mailto:info@sdmbwkudus.sch.id" class="hover:text-[#EAE0CF] transition-colors">info@sdmbwkudus.sch.id</a>
                        </li>
                    </ul>
                </div>

                <!-- Bagian 3: Copyright & Links -->
                <div class="flex flex-col items-center md:items-end gap-4">
                    <div class="text-sm font-medium text-center md:text-right">
                        &copy; {{ currentYear }} SD Muhammadiyah Birrul Walidain Kudus.
                        <br class="hidden md:block"> 
                        <span class="text-xs text-[#94B4C1]">Hak Cipta Dilindungi.</span>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-[#94B4C1]">
                        <span class="hover:text-[#EAE0CF] cursor-pointer transition-colors border-b border-transparent hover:border-[#EAE0CF]">Kebijakan Privasi</span>
                        <span class="h-1 w-1 rounded-full bg-[#547792]"></span>
                        <span class="hover:text-[#EAE0CF] cursor-pointer transition-colors border-b border-transparent hover:border-[#EAE0CF]">Bantuan</span>
                    </div>
                </div>

            </div>

            <!-- Garis Dekorasi Bawah -->
            <div class="mt-12 pt-6 border-t border-[#547792]/10 text-center">
                <p class="text-[10px] uppercase tracking-[0.3em] text-[#547792] font-bold">
                    Membangun Generasi Islami & Berprestasi
                </p>
            </div>
        </div>
    </footer>
</template>