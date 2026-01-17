<script setup>
defineProps({
    pembimbings: {
        type: Array,
        default: () => [],
    },
});

defineEmits(['edit']);
</script>

<template>
    <div class="w-full overflow-hidden rounded-xl border border-[#94B4C1] shadow-lg bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] border-collapse text-left text-sm">
                <!-- Header Tabel -->
                <thead class="bg-[#213448] text-[#EAE0CF]">
                    <tr>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider w-16 text-center">No</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider">Username</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider">Terakhir Login</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-center w-32">Aksi</th>
                    </tr>
                </thead>

                <!-- Body Tabel -->
                <tbody class="divide-y divide-[#94B4C1]/30 text-[#213448]">
                    <tr 
                        v-for="(item, index) in pembimbings" 
                        :key="item.id_pembimbing" 
                        class="transition hover:bg-[#EAE0CF]/30"
                    >
                        <td class="px-6 py-4 font-semibold text-center text-[#547792]">
                            {{ index + 1 }}
                        </td>
                        <td class="px-6 py-4 font-medium">
                            {{ item.nama_lengkap }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs md:text-sm">
                            {{ item.username }}
                        </td>
                        <td class="px-6 py-4 text-[#547792]">
                            <!-- Menampilkan last_login jika ada, jika null tampilkan tanda strip -->
                            {{ item.last_login || '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button 
                                @click="$emit('edit', item)"
                                class="inline-flex items-center gap-1 rounded-lg bg-[#547792] px-3 py-1.5 text-xs font-bold text-[#EAE0CF] transition hover:bg-[#213448] active:scale-95"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </button>
                        </td>
                    </tr>

                    <!-- State Kosong -->
                    <tr v-if="pembimbings.length === 0">
                        <td colspan="5" class="px-6 py-10 text-center text-[#94B4C1] italic">
                            Belum ada data pembimbing.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>