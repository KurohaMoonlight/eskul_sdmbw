<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Eskul;
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

class AbsensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected $idEskul;
    protected $filters;
    protected $namaEskul;
    protected $namaPembimbing;

    public function __construct($idEskul, $filters, $namaEskul)
    {
        $this->idEskul = $idEskul;
        $this->filters = $filters;
        $this->namaEskul = $namaEskul;
        
        // FIX: Gunakan relasi 'pembimbings'
        $eskul = Eskul::with('pembimbings')->find($idEskul);
        
        if ($eskul && $eskul->pembimbings->count() > 0) {
            $this->namaPembimbing = $eskul->pembimbings->pluck('nama_lengkap')->join(', ');
        } else {
            $this->namaPembimbing = '-';
        }
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function collection()
    {
        $queryLog = Absensi::with(['peserta', 'kegiatan', 'nilai_harian'])
            ->whereHas('kegiatan', function($q) {
                $q->where('id_eskul', $this->idEskul);
            });

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $queryLog->whereHas('peserta', function($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%');
            });
        }

        if (!empty($this->filters['status'])) {
            $queryLog->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $startDate = $this->filters['start_date'];
            $endDate = $this->filters['end_date'];
            $queryLog->whereHas('kegiatan', function($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            });
        }

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
            $queryLog->join('kegiatan', 'absensi.id_kegiatan', '=', 'kegiatan.id_kegiatan')
                     ->select('absensi.*')
                     ->orderBy('kegiatan.tanggal', 'desc');
        }

        return $queryLog->get();
    }

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

    public function headings(): array
    {
        return [
            ['LAPORAN ABSENSI & NILAI HARIAN EKSTRAKURIKULER'],
            [''],
            ['Nama Ekstrakurikuler', ': ' . $this->namaEskul],
            ['Nama Pembimbing', ': ' . $this->namaPembimbing],
            ['Tanggal Cetak', ': ' . Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB'],
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

    public function styles(Worksheet $sheet)
    {
        return [
            2 => [
                'font' => ['bold' => true, 'size' => 16, 'name' => 'Calibri', 'color' => ['rgb' => '213448']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            4 => ['font' => ['bold' => true, 'name' => 'Calibri']],
            5 => ['font' => ['bold' => true, 'name' => 'Calibri']],
            6 => ['font' => ['bold' => true, 'name' => 'Calibri']],
            
            8 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '213448']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $highestCol = 'J'; 

                $sheet->mergeCells("A2:{$highestCol}2");
                $sheet->getColumnDimension('A')->setWidth(20); 
                
                $styleThickBorder = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['argb' => '213448'],
                        ],
                    ],
                ];
                $sheet->getStyle("A8:{$highestCol}{$highestRow}")->applyFromArray($styleThickBorder);

                $styleThinBorder = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'CCCCCC'], 
                        ],
                    ],
                ];
                $sheet->getStyle("A9:{$highestCol}{$highestRow}")->applyFromArray($styleThinBorder);

                $sheet->getStyle("A9:{$highestCol}{$highestRow}")->getFont()->setName('Calibri');
                $sheet->getStyle("A9:{$highestCol}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                
                $sheet->getStyle("A9:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C9:H{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->getStyle("B9:B{$highestRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("I9:J{$highestRow}")->getAlignment()->setWrapText(true);

                for ($row = 9; $row <= $highestRow; $row++) {
                    if ($row % 2 != 0) { 
                        $sheet->getStyle("A{$row}:{$highestCol}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('F3F4F6'); 
                    }
                }
            },
        ];
    }
}