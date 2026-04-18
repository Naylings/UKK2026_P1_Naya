<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected array $headings = [];

    public function __construct(
        private readonly array $data,
        private readonly string $type,
    ) {
        if (!empty($this->data)) {
            $this->headings = array_keys((array) $this->data[0]);
        }
    }

    public function array(): array
    {
        return array_map(fn($row) => array_values((array) $row), $this->data);
    }

    public function headings(): array
    {
        return array_map(
            fn($h) => ucfirst(str_replace('_', ' ', $h)),
            $this->headings
        );
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $range = "A1:{$highestColumn}{$highestRow}";
                $headerRange = "A1:{$highestColumn}1";

                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '366092'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);

                $sheet->freezePane('A2');

                $sheet->setAutoFilter($headerRange);

                $sheet->getStyle($range)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('DDDDDD');

                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F3F6FB');
                    }
                }

                $sheet->getRowDimension(1)->setRowHeight(26);

                foreach ($this->headings as $i => $h) {
                    if (str_contains(strtolower($h), 'id')) {
                        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                        $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                            ->getAlignment()->setHorizontal('center');
                    }
                }
            },
        ];
    }
}