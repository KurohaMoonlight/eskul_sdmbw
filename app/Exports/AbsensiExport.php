<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Eskul; // Tambahkan Model Eskul
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class AbsensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $idEskul;
    protected $filters;
    protected $namaEskul;
    protected $namaPembimbing; // Properti baru untuk nama pembimbing

    public function __construct($idEskul, $filters, $namaEskul)
    {
        $this->idEskul = $idEskul;
        $this->filters = $filters;
        $this->namaEskul = $namaEskul;
        
        // Ambil data Pembimbing berdasarkan ID Eskul
        $eskul = Eskul::with('pembimbing')->find($idEskul);
        $this->namaPembimbing = $eskul->pembimbing ? $eskul->pembimbing->nama_lengkap : '-';
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // 1. Base Query dengan Eager Loading
        $queryLog = Absensi::with(['peserta', 'kegiatan', 'nilai_harian'])
            ->whereHas('kegiatan', function($q) {
                $q->where('id_eskul', $this->idEskul);
            });

        // 2. Filter Search Nama
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $queryLog->whereHas('peserta', function($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%');
            });
        }

        // 3. Filter Status
        if (!empty($this->filters['status'])) {
            $queryLog->where('status', $this->filters['status']);
        }

        // 4. Filter Tanggal
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $startDate = $this->filters['start_date'];
            $endDate = $this->filters['end_date'];
            $queryLog->whereHas('kegiatan', function($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            });
        }

        // 5. Filter & Sorting Score Mode
        if (!empty($this->filters['score_mode'])) {
            $scoreMode = $this->filters['score_mode'];
            
            $queryLog->leftJoin('nilai_harian', 'absensi.id_absensi', '=', 'nilai_harian.id_absensi')
                     ->select('absensi.*');

            $rawAvgScore = '(COALESCE(nilai_harian.skor_teknik, 0) + COALESCE(nilai_harian.skor_disiplin, 0) + COALESCE(nilai_harian.skor_kerjasama, 0)) / 3';

            if ($scoreMode === 'highest') {
                $queryLog->orderByRaw("$rawAvgScore DESC");
            } elseif ($scoreMode === 'lowest') {
                $queryLog->where('absensi.status', 'Hadir')
                         ->orderByRaw("$rawAvgScore ASC");
            } elseif ($scoreMode === 'under_70') {
                $queryLog->where('absensi.status', 'Hadir')
                         ->whereRaw("$rawAvgScore < 70")
                         ->orderByRaw("$rawAvgScore ASC");
            }
        } else {
            // Default Sorting
            $queryLog->join('kegiatan', 'absensi.id_kegiatan', '=', 'kegiatan.id_kegiatan')
                     ->select('absensi.*')
                     ->orderBy('kegiatan.tanggal', 'desc');
        }

        return $queryLog->get();
    }

    /**
     * Mapping data per baris
     */
    public function map($row): array
    {
        $teknik = $row->nilai_harian->skor_teknik ?? 0;
        $disiplin = $row->nilai_harian->skor_disiplin ?? 0;
        $kerjasama = $row->nilai_harian->skor_kerjasama ?? 0;
        
        $avg = 0;
        if ($row->status === 'Hadir') {
            $avg = round(($teknik + $disiplin + $kerjasama) / 3, 2);
        }

        $tanggal = Carbon::parse($row->kegiatan->tanggal)->translatedFormat('d F Y');

        return [
            $tanggal,
            $row->peserta->nama_lengkap,
            $row->peserta->tingkat_kelas,
            $row->status,
            $row->status === 'Hadir' ? $teknik : '-',
            $row->status === 'Hadir' ? $disiplin : '-',
            $row->status === 'Hadir' ? $kerjasama : '-',
            $row->status === 'Hadir' ? $avg : '-',
            $row->nilai_harian->catatan_harian ?? '-',
            $row->kegiatan->materi_kegiatan,
        ];
    }

    /**
     * Header Kolom
     */
    public function headings(): array
    {
        return [
            ['LAPORAN ABSENSI & NILAI HARIAN EKSTRAKURIKULER'],
            ['Eskul: ' . $this->namaEskul],
            ['Pembimbing: ' . $this->namaPembimbing], // Tambahan Nama Pembimbing
            ['Dicetak Pada: ' . Carbon::now()->translatedFormat('d F Y H:i')],
            [''], 
            [     
                'TANGGAL',
                'NAMA SISWA',
                'KELAS',
                'STATUS',
                'TEKNIK',
                'DISIPLIN',
                'KERJASAMA',
                'RATA-RATA',
                'CATATAN',
                'MATERI KEGIATAN'
            ]
        ];
    }

    /**
     * Styling Dasar
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true, 'size' => 12]],
            4 => ['font' => ['italic' => true, 'size' => 10]],
            
            // Header Tabel (Sekarang di baris 6)
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '213448'] // Warna Header Biru Gelap
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Event untuk styling lanjutan
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();

                // Merge Title Cells
                $sheet->mergeCells('A1:J1');
                $sheet->mergeCells('A2:J2');
                $sheet->mergeCells('A3:J3');
                $sheet->mergeCells('A4:J4');

                // Alignment Left untuk Informasi Header
                $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Styling Tabel Data (Border & Zebra Striping)
                $styleBorder = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ];
                
                // Terapkan border dari header tabel (A6) sampai bawah
                $sheet->getStyle('A6:J' . $highestRow)->applyFromArray($styleBorder);
                
                // Alignment Data Tabel
                $sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tanggal
                $sheet->getStyle('C7:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Kelas, Status, Nilai
                $sheet->getStyle('I7:J' . $highestRow)->getAlignment()->setWrapText(true); // Catatan & Materi

                // Tambahkan Zebra Striping (Warna selang-seling) untuk baris data
                for ($row = 7; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) { // Baris Genap
                        $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('F9FAFB'); // Abu-abu sangat muda (cool gray 50)
                    }
                }
            },
        ];
    }
}