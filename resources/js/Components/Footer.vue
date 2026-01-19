<script setup>
// Mengambil tahun saat ini secara otomatis
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="w-full bg-[#213448] py-8 text-[#EAE0CF] border-t border-[#547792]/30">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                
                <!-- Bagian Kiri: Branding -->
                <div class="flex flex-col items-center md:items-start">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-lg font-bold tracking-tight">SD Muhammadiyah Birrul Walidain</span>
                    </div>
                    <p class="text-sm text-[#94B4C1] text-center md:text-left">
                        Sistem Informasi Ekstrakurikuler Birrul Walidain.
                    </p>
                </div>

                <!-- Bagian Kanan: Copyright & Tahun Otomatis -->
                <div class="flex flex-col items-center md:items-end gap-2">
                    <div class="text-sm font-medium">
                        &copy; {{ currentYear }} Hak Cipta Dilindungi.
                    </div>
                    <div class="flex items-center gap-4 text-xs text-[#94B4C1]">
                        <span class="hover:text-[#EAE0CF] cursor-pointer transition-colors">Kebijakan Privasi</span>
                        <span class="h-1 w-1 rounded-full bg-[#547792]"></span>
                        <span class="hover:text-[#EAE0CF] cursor-pointer transition-colors">Bantuan</span>
                    </div>
                </div>

            </div>

            <!-- Garis Dekorasi Bawah -->
            <div class="mt-8 pt-6 border-t border-[#547792]/10 text-center">
                <p class="text-[10px] uppercase tracking-[0.2em] text-[#547792] font-bold">
                    Membangun Generasi Islami & Berprestasi
                </p>
            </div>
        </div>
    </footer>
</template>