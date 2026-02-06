<?php

namespace App\Exports;

use App\Models\Nilai;
use App\Models\Eskul;
use App\Models\Kegiatan;
use App\Models\Absensi;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class NilaiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected $idEskul;
    protected $semester;
    protected $tahunAjaran;
    
    // Data Pendukung untuk Header
    protected $namaEskul;
    protected $namaPembimbing;
    protected $idKegiatanSemester; // Untuk hitung absensi

    public function __construct($idEskul, $semester, $tahunAjaran)
    {
        $this->idEskul = $idEskul;
        $this->semester = $semester;
        $this->tahunAjaran = $tahunAjaran;

        // PERBAIKAN: Gunakan relasi 'pembimbings' (plural) sesuai Model Eskul baru
        // Menggunakan find() lalu load() atau with() langsung
        $eskul = Eskul::with('pembimbings')->find($idEskul);
        
        $this->namaEskul = $eskul ? $eskul->nama_eskul : '-';
        
        // Logika Baru: Mengambil banyak nama pembimbing dan digabung dengan koma
        // Pastikan cek relasi pembimbings ada dan tidak kosong
        if ($eskul && $eskul->pembimbings && $eskul->pembimbings->count() > 0) {
            $this->namaPembimbing = $eskul->pembimbings->pluck('nama_lengkap')->join(', ');
        } else {
            $this->namaPembimbing = '-';
        }

        // Pre-fetch ID Kegiatan dalam semester ini untuk efisiensi query kehadiran
        $dateRange = $this->getSemesterDateRange($semester, $tahunAjaran);
        $this->idKegiatanSemester = Kegiatan::where('id_eskul', $idEskul)
            ->whereBetween('tanggal', $dateRange)
            ->pluck('id_kegiatan');
    }

    public function startCell(): string
    {
        return 'A2'; // Margin atas
    }

    private function getSemesterDateRange($semester, $tahunAjaran)
    {
        $years = explode('/', $tahunAjaran);
        if (count($years) < 2) {
             $y = date('Y');
             $years = [$y, $y+1];
        }
        $startYear = (int)$years[0];
        $endYear = (int)$years[1];

        if ($semester === 'Ganjil') {
            return [
                Carbon::create($startYear, 7, 1)->startOfDay()->format('Y-m-d'),
                Carbon::create($startYear, 12, 31)->endOfDay()->format('Y-m-d')
            ];
        } else {
            return [
                Carbon::create($endYear, 1, 1)->startOfDay()->format('Y-m-d'),
                Carbon::create($endYear, 6, 30)->endOfDay()->format('Y-m-d')
            ];
        }
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Nilai::with(['anggota_eskul.peserta'])
            ->where('id_eskul', $this->idEskul)
            ->where('semester', $this->semester)
            ->where('tahun_ajaran', $this->tahunAjaran)
            ->get()
            ->sortBy(function($item) {
                // Sort berdasarkan nama peserta
                return $item->anggota_eskul->peserta->nama_lengkap;
            });
    }

    /**
     * Mapping data per baris
     */
    public function map($row): array
    {
        // 1. Hitung Nilai Akhir
        $t = $row->nilai_teknik ?? 0;
        $d = $row->nilai_disiplin ?? 0;
        $k = $row->nilai_kerjasama ?? 0;
        $akhir = round(($t + $d + $k) / 3);

        // 2. Tentukan Predikat
        $predikat = 'D';
        if ($akhir >= 90) $predikat = 'A';
        elseif ($akhir >= 80) $predikat = 'B';
        elseif ($akhir >= 70) $predikat = 'C';

        // 3. Hitung Persentase Kehadiran
        $idPeserta = $row->anggota_eskul->id_peserta;
        $totalPertemuan = $this->idKegiatanSemester->count();
        $hadir = 0;
        $persenHadir = 0;

        if ($totalPertemuan > 0) {
            $hadir = Absensi::whereIn('id_kegiatan', $this->idKegiatanSemester)
                ->where('id_peserta', $idPeserta)
                ->where('status', 'Hadir')
                ->count();
            $persenHadir = round(($hadir / $totalPertemuan) * 100);
        }

        return [
            $row->anggota_eskul->peserta->nama_lengkap,
            $row->anggota_eskul->peserta->tingkat_kelas,
            $row->anggota_eskul->peserta->jenis_kelamin,
            $persenHadir . '%', // Kolom Kehadiran
            $t,
            $d,
            $k,
            $akhir,
            $predikat,
            $row->catatan_rapor ?? '-'
        ];
    }

    /**
     * Header Kolom
     */
    public function headings(): array
    {
        return [
            ['REKAPITULASI NILAI EKSTRAKURIKULER'],
            [''],
            ['Nama Ekstrakurikuler', ': ' . $this->namaEskul],
            ['Nama Pembimbing', ': ' . $this->namaPembimbing],
            ['Periode Semester', ': ' . $this->semester . ' ' . $this->tahunAjaran],
            ['Tanggal Cetak', ': ' . Carbon::now()->translatedFormat('d F Y')],
            [''], // Spasi
            [     // Header Tabel Data
                'NAMA SISWA',
                'KELAS',
                'L/P',
                'KEHADIRAN',
                'TEKNIK',
                'DISIPLIN',
                'KERJASAMA',
                'NILAI AKHIR',
                'PREDIKAT',
                'CATATAN'
            ]
        ];
    }

    /**
     * Styling
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true, 'size' => 12]],
            4 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '547792']]], // Warna biru muda
            
            // Header Tabel (Baris 9)
            9 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '213448'] // Biru Gelap
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Event Styling Lanjutan (Border & Zebra)
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $highestCol = 'J';

                // Merge Titles
                $sheet->mergeCells("A2:{$highestCol}2");

                // Border Seluruh Tabel
                $styleBorder = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ];
                $sheet->getStyle('A9:J' . $highestRow)->applyFromArray($styleBorder);

                // Alignment Center untuk Kolom Angka & Status
                // B: Kelas, C: LP, D: Hadir, E-I: Nilai
                $sheet->getStyle('B10:I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Wrap Text untuk Catatan (Kolom J)
                $sheet->getStyle('J10:J' . $highestRow)->getAlignment()->setWrapText(true);

                // Zebra Striping
                for ($row = 10; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('F9FAFB');
                    }
                }
            },
        ];
    }
}