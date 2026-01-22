<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use App\Models\Eskul;
use App\Models\Jadwal;
use App\Models\AnggotaEskul;
use App\Models\Kegiatan; // Tambahkan Import Model Kegiatan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Absensi;
use App\Models\Prestasi; // Tambahkan import Model Prestasi
use Illuminate\Support\Facades\Storage; // Pastikan ini diimport

class PembimbingController extends Controller
{
    /**
     * Menampilkan Dashboard Pembimbing.
     */
    public function dashboard()
    {
        $idPembimbing = Auth::guard('pembimbing')->id();
        $listEskul = Eskul::where('id_pembimbing', $idPembimbing)->get();

        return Inertia::render('Pembimbing/Dashboard', [
            'eskul_list' => $listEskul
        ]);
    }

    /**
     * Menampilkan Detail Eskul (Jadwal, Anggota, & Kegiatan).
     */
    public function show(Request $request, $id)
    {
        $idPembimbing = Auth::guard('pembimbing')->id();

        // 1. Validasi Kepemilikan Eskul
        $eskul = Eskul::where('id_eskul', $id)
                      ->where('id_pembimbing', $idPembimbing)
                      ->firstOrFail();

        // 2. Data Dasar
        $jadwal = Jadwal::where('id_eskul', $eskul->id_eskul)->get();
        $anggota = AnggotaEskul::with('peserta')
                    ->where('id_eskul', $eskul->id_eskul)
                    ->get();
        $kegiatan = Kegiatan::where('id_eskul', $eskul->id_eskul)
                    ->orderBy('tanggal', 'desc')
                    ->get();

        // 3. LOGIKA FILTER LOG ABSENSI
        $queryLog = Absensi::with(['peserta', 'kegiatan'])
            ->whereHas('kegiatan', function($q) use ($id) {
                $q->where('id_eskul', $id);
            });

        // Filter: Search Nama Siswa
        if ($request->search) {
            $queryLog->whereHas('peserta', function($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
            });
        }

        // Filter: Status
        if ($request->status) {
            $queryLog->where('status', $request->status);
        }

        // Filter: Rentang Tanggal
        if ($request->start_date && $request->end_date) {
            $queryLog->whereHas('kegiatan', function($q) use ($request) {
                $q->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            });
        }

        // Clone query untuk statistik sebelum dipaginate
        $allLogs = $queryLog->get(); 
        
        // Paginasi Log
        $logs = (clone $queryLog)
            ->join('kegiatan', 'absensi.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->orderBy('kegiatan.tanggal', 'desc')
            ->select('absensi.*') // Pastikan select tabel utama agar tidak tertimpa join
            ->paginate(10)
            ->withQueryString();

        // Hitung Statistik
        $totalData = $allLogs->count();
        $totalHadir = $allLogs->where('status', 'Hadir')->count();
        
        $summary = [
            'total_pertemuan' => $allLogs->pluck('id_kegiatan')->unique()->count(),
            'avg_kehadiran'   => $totalData > 0 ? round(($totalHadir / $totalData) * 100, 1) : 0,
            'total_sakit'     => $allLogs->where('status', 'Sakit')->count(),
            'total_izin'      => $allLogs->where('status', 'Izin')->count(),
            'total_alpha'     => $allLogs->where('status', 'Alpha')->count(),
        ];

        // 4. AMBIL DATA PRESTASI (BARU)
        $prestasi = Prestasi::with('peserta') // Load relasi peserta untuk nama siswa
            ->where('id_eskul', $eskul->id_eskul)
            ->orderBy('tanggal_lomba', 'desc')
            ->get();

        return Inertia::render('Pembimbing/EskulCardDetail', [
            'eskul'      => $eskul,
            'jadwal'     => $jadwal,
            'anggota'    => $anggota,
            'kegiatan'   => $kegiatan,
            'logs'       => $logs,
            'logSummary' => $summary,
            'filters'    => $request->only(['search', 'start_date', 'end_date', 'status']),
            'prestasi'   => $prestasi, // <-- INI YANG HARUS DITAMBAHKAN
        ]);
    }
    // --- Method CRUD Pembimbing (jika diperlukan untuk manajemen user) ---

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pembimbing,username',
            'password'     => 'required|string|min:6',
        ]);

        Pembimbing::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'username'     => $validated['username'],
            'password'     => Hash::make($validated['password']),
        ]);

        return back();
    }

    public function update(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('pembimbing')->ignore($pembimbing->id_pembimbing, 'id_pembimbing')
            ],
            'password'     => 'nullable|string|min:6',
        ]);

        $dataToUpdate = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'username'     => $validated['username'],
        ];

        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
        }

        $pembimbing->update($dataToUpdate);

        return back();
    }

    public function destroy($id)
    {
        $prestasi = Prestasi::findOrFail($id);

        // Hapus file fisik dari storage jika ada
        if ($prestasi->foto_prestasi) {
            Storage::disk('public')->delete($prestasi->foto_prestasi);
        }

        $prestasi->delete();

        return back()->with('success', 'Prestasi dihapus.');
    }
}