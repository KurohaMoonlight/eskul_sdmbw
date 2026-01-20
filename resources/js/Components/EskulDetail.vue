<div class="w-full lg:w-[40%]">
                            <div class="rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden h-full flex flex-col">
                                <div class="bg-[#213448] px-6 py-4 flex items-center justify-between">
                                    <h3 class="text-[#EAE0CF] font-bold text-lg flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Jadwal Latihan
                                    </h3>
                                    <span class="bg-[#94B4C1]/20 text-[#94B4C1] text-xs px-2 py-1 rounded-md font-medium">
                                        {{ props.jadwal.length }} Sesi
                                    </span>
                                </div>

                                <div class="flex-1 flex flex-col">
                                    <div v-if="props.jadwal.length > 0" class="divide-y divide-gray-100">
                                        <div v-for="item in props.jadwal" :key="item.id_jadwal" class="p-4 hover:bg-gray-50 transition-colors flex items-center gap-4">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#213448]/5 text-[#213448]">
                                                <span class="text-xs font-bold uppercase">{{ item.hari ? item.hari.substring(0, 3) : '...' }}</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-800 truncate">{{ item.lokasi || 'Lokasi Belum Diatur' }}</p>
                                                <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                                    {{ formatTime(item.jam_mulai) }} - {{ formatTime(item.jam_selesai) }}
                                                </p>
                                            </div>
                                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-[10px] font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                Kls {{ item.kelas_min }}-{{ item.kelas_max }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-else class="p-8 flex flex-col justify-center items-center text-center space-y-4 h-full min-h-[200px]">
                                        <div class="bg-gray-50 rounded-full p-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 font-medium text-sm">Belum ada jadwal.</p>
                                    </div>
                                </div>

                                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                                    <button @click="openTambahJadwal" class="w-full rounded-lg bg-[#547792] px-4 py-2 text-sm font-bold text-[#EAE0CF] shadow-md transition hover:bg-[#213448] flex items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Tambah Jadwal
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 2. BOX ANGGOTA (Kanan - 60%) -->
                        <div class="w-full lg:w-[60%]">
                            <div class="rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden h-full flex flex-col">
                                <div class="bg-[#213448] px-6 py-4 flex items-center justify-between">
                                    <h3 class="text-[#EAE0CF] font-bold text-lg flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#94B4C1]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        Daftar Anggota
                                    </h3>
                                    <span class="bg-[#94B4C1]/20 text-[#94B4C1] text-xs px-2 py-1 rounded-md font-medium">
                                        {{ props.anggota.length }} Peserta
                                    </span>
                                </div>

                                <div class="flex-1 flex flex-col">
                                    <div v-if="props.anggota.length > 0" class="divide-y divide-gray-100 max-h-[400px] overflow-y-auto">
                                        <div v-for="item in props.anggota" :key="item.id_anggota" class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#547792]/10 text-[#547792] font-bold text-sm border border-[#547792]/20">
                                                    {{ item.peserta?.nama_lengkap?.charAt(0) || '?' }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-gray-800">{{ item.peserta?.nama_lengkap || 'Nama Peserta' }}</p>
                                                    <p class="text-xs text-gray-500">Kelas {{ item.peserta?.tingkat_kelas || '-' }} • {{ item.tahun_ajaran }}</p>
                                                </div>
                                            </div>
                                            <span :class="item.status_aktif ? 'bg-emerald-50 text-emerald-600 ring-emerald-600/20' : 'bg-red-50 text-red-600 ring-red-600/20'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-medium ring-1 ring-inset">
                                                {{ item.status_aktif ? 'Aktif' : 'Non-Aktif' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-else class="p-8 flex flex-col justify-center items-center text-center space-y-4 h-full min-h-[200px]">
                                        <div class="bg-gray-50 rounded-full p-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 font-medium text-sm">Belum ada anggota.</p>
                                    </div>
                                </div>

                                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                                    <button @click="openTambahAnggota" class="w-full rounded-lg bg-white border border-[#94B4C1] px-4 py-2 text-sm font-bold text-[#547792] shadow-sm transition hover:bg-[#547792] hover:text-white flex items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                        Tambah Anggota
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>

        <!-- MOUNT MODAL -->
        <ModalFormJadwal 
            v-if="showModalJadwal"
            :show="showModalJadwal"
            :jadwalData="null" 
            :idEskul="idEskul" 
            @close="showModalJadwal = false"
        />

        <ModalFormAddAnggota 
            v-if="showModalAnggota"
            :show="showModalAnggota"
            :idEskul="idEskul"
            :anggotaData="null"
            @close="showModalAnggota = false"
        />