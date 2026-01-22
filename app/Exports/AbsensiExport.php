<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Eskul; // Import model Eskul untuk ambil nama pembimbing
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

class AbsensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $idEskul;
    protected $filters;
    protected $eskulName;
    protected $pembimbingName; // Tambahan properti nama pembimbing

    public function __construct($idEskul, $filters, $eskulName)
    {
        $this->idEskul = $idEskul;
        $this->filters = $filters;
        $this->eskulName = $eskulName;

        // Ambil nama pembimbing dari relasi Eskul
        $eskul = Eskul::with('pembimbing')->find($idEskul);
        $this->pembimbingName = $eskul->pembimbing ? $eskul->pembimbing->nama_lengkap : '-';
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Absensi::with(['peserta', 'kegiatan'])
            ->whereHas('kegiatan', function($q) {
                $q->where('id_eskul', $this->idEskul);
            });

        // Filter Search
        if (!empty($this->filters['search'])) {
            $query->whereHas('peserta', function($q) {
                $q->where('nama_lengkap', 'like', '%' . $this->filters['search'] . '%');
            });
        }

        // Filter Status
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Filter Tanggal
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereHas('kegiatan', function($q) {
                $q->whereBetween('tanggal', [$this->filters['start_date'], $this->filters['end_date']]);
            });
        }

        return $query->join('kegiatan', 'absensi.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->orderBy('kegiatan.tanggal', 'desc')
            ->select('absensi.*')
            ->get();
    }

    public function map($absensi): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            \Carbon\Carbon::parse($absensi->kegiatan->tanggal)->format('d-m-Y'),
            $absensi->peserta->nama_lengkap,
            $absensi->peserta->tingkat_kelas,
            strtoupper($absensi->status),
            $absensi->kegiatan->materi_kegiatan ?? '-',
        ];
    }

    public function headings(): array
    {
        // Custom Header: Baris 1-4 untuk Judul, Baris 5 untuk Header Tabel
        return [
            ['LAPORAN ABSENSI EKSTRAKURIKULER'],
            [strtoupper($this->eskulName)],
            ['SD MUHAMMADIYAH BONDOWOSO'],
            [' '], // Spasi
            ['NO', 'TANGGAL', 'NAMA SISWA', 'KELAS', 'STATUS', 'MATERI KEGIATAN'], // Header Tabel Data
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Styling Judul (Baris 1, 2, 3)
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            
            // Styling Header Tabel (Baris 5)
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '213448'] // Warna Biru Tema
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow(); // Baris terakhir data
                $highestColumn = $sheet->getHighestColumn();

                // 1. Merge Cells untuk Judul (A1 sampai F1, dst)
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');

                // 2. Tambahkan Border ke Seluruh Tabel Data (Mulai baris 5 sampai akhir data)
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ];
                $sheet->getStyle('A5:' . $highestColumn . $highestRow)->applyFromArray($styleArray);

                // 3. Center Alignment untuk Kolom Tertentu
                $sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
                $sheet->getStyle('B6:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tanggal
                $sheet->getStyle('D6:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Kelas
                $sheet->getStyle('E6:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status

                // 4. Conditional Formatting: Warna Cell Status
                $totalHadir = 0;
                $totalSakit = 0;
                $totalIzin = 0;
                $totalAlpha = 0;
                $totalData = $highestRow - 5; // Total baris data (dikurangi 5 baris header)

                for ($row = 6; $row <= $highestRow; $row++) {
                    $statusCell = $sheet->getCell('E' . $row);
                    $statusValue = $statusCell->getValue();
                    $color = 'FFFFFF'; 

                    if ($statusValue === 'HADIR') {
                        $color = 'D1FAE5'; $totalHadir++;
                    } elseif ($statusValue === 'SAKIT') {
                        $color = 'FEF3C7'; $totalSakit++;
                    } elseif ($statusValue === 'IZIN') {
                        $color = 'DBEAFE'; $totalIzin++;
                    } elseif ($statusValue === 'ALPHA') {
                        $color = 'FEE2E2'; $totalAlpha++;
                    }

                    $sheet->getStyle('E' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($color);
                }

                // --- BAGIAN BARU: FOOTER & STATISTIK ---
                
                $footerRow = $highestRow + 2; // Mulai 2 baris setelah data terakhir

                // A. Tanda Tangan & Tanggal Cetak
                $dateNow = \Carbon\Carbon::now()->isoFormat('D MMMM Y');
                
                // Set Tanggal Cetak di sebelah kanan
                $sheet->setCellValue('E' . $footerRow, 'Bondowoso, ' . $dateNow);
                $sheet->mergeCells('E' . $footerRow . ':F' . $footerRow); // Merge biar muat
                
                $sheet->setCellValue('E' . ($footerRow + 1), 'Pembimbing Ekstrakurikuler');
                $sheet->mergeCells('E' . ($footerRow + 1) . ':F' . ($footerRow + 1));

                // Nama Pembimbing (dengan garis bawah/underline)
                $sheet->setCellValue('E' . ($footerRow + 5), $this->pembimbingName);
                $sheet->mergeCells('E' . ($footerRow + 5) . ':F' . ($footerRow + 5));
                $sheet->getStyle('E' . ($footerRow + 5))->getFont()->setBold(true)->setUnderline(true);
                
                // Alignment Center untuk Tanda Tangan
                $sheet->getStyle('E' . $footerRow . ':E' . ($footerRow + 5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


                // B. Statistik Ringkas & Grafik Batang (Di sebelah kiri footer)
                // Kita buat visualisasi sederhana dengan background cell sebagai bar chart
                
                $statsStartRow = $footerRow;
                $sheet->setCellValue('A' . $statsStartRow, 'RINGKASAN KEHADIRAN');
                $sheet->getStyle('A' . $statsStartRow)->getFont()->setBold(true);

                // Hitung Persentase
                $persenHadir = $totalData > 0 ? round(($totalHadir / $totalData) * 100) : 0;
                
                // Baris Statistik Hadir
                $sheet->setCellValue('A' . ($statsStartRow + 1), 'Hadir');
                $sheet->setCellValue('B' . ($statsStartRow + 1), $totalHadir . ' (' . $persenHadir . '%)');
                // Visualisasi Bar Chart Hadir (Cell C diwarnai hijau sesuai persentase - simulasi kasar)
                // Karena excel cell tidak bisa partial fill mudah, kita warnai full jika > 0
                if ($totalHadir > 0) {
                    $sheet->getStyle('C' . ($statsStartRow + 1))->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('D1FAE5'); // Hijau
                    $sheet->setCellValue('C' . ($statsStartRow + 1), str_repeat('|', $persenHadir / 5)); // Grafik text manual
                    $sheet->getStyle('C' . ($statsStartRow + 1))->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('10B981'));
                }

                // Baris Statistik Sakit/Izin
                $sheet->setCellValue('A' . ($statsStartRow + 2), 'Sakit/Izin');
                $sheet->setCellValue('B' . ($statsStartRow + 2), ($totalSakit + $totalIzin));
                
                // Baris Statistik Alpha
                $sheet->setCellValue('A' . ($statsStartRow + 3), 'Alpha');
                $sheet->setCellValue('B' . ($statsStartRow + 3), $totalAlpha);
                if ($totalAlpha > 0) {
                    $sheet->getStyle('C' . ($statsStartRow + 3))->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FEE2E2'); // Merah
                    $sheet->setCellValue('C' . ($statsStartRow + 3), 'Perlu Perhatian');
                    $sheet->getStyle('C' . ($statsStartRow + 3))->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('EF4444'));
                }

                // Border kotak statistik
                $sheet->getStyle('A' . $statsStartRow . ':C' . ($statsStartRow + 3))->applyFromArray([
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_THIN]
                    ]
                ]);

            },
        ];
    }
}