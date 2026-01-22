<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi</title>
    <style>
        /* CSS sederhana agar tetap terbaca jika dibuka di browser biasa */
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f0f0f0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <!-- Header Laporan -->
    <table>
        <tr>
            <td colspan="6" class="text-center font-bold" style="font-size: 16px;">LAPORAN ABSENSI EKSTRAKURIKULER</td>
        </tr>
        <tr>
            <td colspan="6" class="text-center font-bold" style="font-size: 14px;">{{ strtoupper($eskul->nama_eskul) }}</td>
        </tr>
        <tr>
            <td colspan="6" class="text-center">SD Muhammadiyah Bondowoso</td>
        </tr>
        <tr>
            <td colspan="6"></td> <!-- Spacer -->
        </tr>
        <tr>
            <td colspan="6">
                <strong>Periode:</strong> 
                {{ $filter['start_date'] ? date('d/m/Y', strtotime($filter['start_date'])) : 'Awal' }} 
                s/d 
                {{ $filter['end_date'] ? date('d/m/Y', strtotime($filter['end_date'])) : 'Akhir' }}
            </td>
        </tr>
        <tr>
            <td colspan="6">
                <strong>Ringkasan:</strong> 
                Total Pertemuan: {{ $summary['total_pertemuan'] }} | 
                Hadir: {{ $summary['hadir'] }} | 
                Sakit: {{ $summary['sakit'] }} | 
                Izin: {{ $summary['izin'] }} | 
                Alpha: {{ $summary['alpha'] }}
            </td>
        </tr>
        <tr>
            <td colspan="6"></td> <!-- Spacer -->
        </tr>
    </table>

    <!-- Tabel Data -->
    <!-- border="1" wajib agar Excel merender garis tabel -->
    <table border="1">
        <thead>
            <tr>
                <th style="background-color: #d1d5db; width: 50px;">No</th>
                <th style="background-color: #d1d5db; width: 120px;">Tanggal</th>
                <th style="background-color: #d1d5db; width: 200px;">Nama Siswa</th>
                <th style="background-color: #d1d5db; width: 80px;">Kelas</th>
                <th style="background-color: #d1d5db; width: 100px;">Status</th>
                <th style="background-color: #d1d5db; width: 250px;">Materi Kegiatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($log->kegiatan->tanggal)->format('d-m-Y') }}</td>
                <td>{{ $log->peserta->nama_lengkap }}</td>
                <td class="text-center">{{ $log->peserta->tingkat_kelas }}</td>
                <td class="text-center">{{ $log->status }}</td>
                <td>{{ $log->kegiatan->materi_kegiatan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px;">
                    Tidak ada data absensi pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer Tanda Tangan -->
    <table>
        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td colspan="2" class="text-center">Bondowoso, {{ date('d F Y') }}</td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td colspan="2" class="text-center">Pembimbing,</td>
        </tr>
        <tr>
            <td colspan="6" style="height: 50px;"></td> <!-- Spasi Tanda Tangan -->
        </tr>
        <tr>
            <td colspan="4"></td>
            <td colspan="2" class="text-center font-bold">
                <u>{{ $eskul->pembimbing->nama_lengkap ?? '.........................' }}</u>
            </td>
        </tr>
    </table>

</body>
</html>