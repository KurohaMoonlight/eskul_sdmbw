<?php

namespace App\Exports;

use App\Models\Nilai;
use App\Models\Eskul;
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

class NilaiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $idEskul;
    protected $semester;
    protected $tahunAjaran;
    protected $eskulName;
    protected $pembimbingName;

    public function __construct($idEskul, $semester, $tahunAjaran, $eskulName, $pembimbingName)
    {
        $this->idEskul = $idEskul;
        $this->semester = $semester;
        $this->tahunAjaran = $tahunAjaran;
        $this->eskulName = $eskulName;
        $this->pembimbingName = $pembimbingName;
    }

    public function collection()
    {
        // Ambil data nilai beserta relasi peserta
        return Nilai::with(['anggota_eskul.peserta'])
            ->where('id_eskul', $this->idEskul)
            ->where('semester', $this->semester)
            ->where('tahun_ajaran', $this->tahunAjaran)
            ->get()
            // Urutkan berdasarkan nama siswa
            ->sortBy(function($nilai) {
                return $nilai->anggota_eskul->peserta->nama_lengkap;
            });
    }

    public function map($nilai): array
    {
        static $no = 0;
        $no++;

        // Hitung Predikat
        $avg = ($nilai->nilai_disiplin + $nilai->nilai_teknik + $nilai->nilai_kerjasama) / 3;
        $predikat = 'D';
        if ($avg >= 90) $predikat = 'A';
        elseif ($avg >= 80) $predikat = 'B';
        elseif ($avg >= 70) $predikat = 'C';

        return [
            $no,
            $nilai->anggota_eskul->peserta->nama_lengkap,
            $nilai->anggota_eskul->peserta->tingkat_kelas,
            $nilai->nilai_disiplin,
            $nilai->nilai_teknik,
            $nilai->nilai_kerjasama,
            $predikat, // Kolom Predikat (Hitung otomatis)
            $nilai->catatan_rapor ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            ['DAFTAR NILAI EKSTRAKURIKULER'],
            [strtoupper($this->eskulName)],
            ['TAHUN AJARAN ' . $this->tahunAjaran . ' - SEMESTER ' . strtoupper($this->semester)],
            [' '],
            ['NO', 'NAMA SISWA', 'KELAS', 'DISIPLIN', 'TEKNIK', 'KERJASAMA', 'PREDIKAT', 'CATATAN'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Judul
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            
            // Header Tabel
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '213448']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();

                // Merge Header
                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                $sheet->mergeCells('A3:H3');

                // Border Tabel
                $styleArray = [
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ];
                $sheet->getStyle('A5:' . $lastCol . $lastRow)->applyFromArray($styleArray);

                // Center Alignment Kolom Angka & Predikat
                $sheet->getStyle('A6:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
                $sheet->getStyle('C6:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Kelas, Nilai, Predikat

                // Conditional Formatting untuk Predikat (Kolom G)
                for ($row = 6; $row <= $lastRow; $row++) {
                    $predikat = $sheet->getCell('G' . $row)->getValue();
                    $color = 'FFFFFF';
                    if ($predikat === 'A') $color = 'D1FAE5'; // Hijau
                    elseif ($predikat === 'B') $color = 'DBEAFE'; // Biru
                    elseif ($predikat === 'C') $color = 'FEF3C7'; // Kuning
                    elseif ($predikat === 'D') $color = 'FEE2E2'; // Merah

                    $sheet->getStyle('G' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
                }

                // Tanda Tangan
                $footerRow = $lastRow + 3;
                $sheet->setCellValue('G' . $footerRow, 'Kudus, ' . date('d F Y'));
                $sheet->mergeCells('G' . $footerRow . ':H' . $footerRow);
                
                $sheet->setCellValue('G' . ($footerRow + 1), 'Pembimbing Ekstrakurikuler');
                $sheet->mergeCells('G' . ($footerRow + 1) . ':H' . ($footerRow + 1));

                $sheet->setCellValue('G' . ($footerRow + 5), $this->pembimbingName);
                $sheet->mergeCells('G' . ($footerRow + 5) . ':H' . ($footerRow + 5));
                $sheet->getStyle('G' . ($footerRow + 5))->getFont()->setBold(true)->setUnderline(true);
                $sheet->getStyle('G' . $footerRow . ':H' . ($footerRow + 5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}