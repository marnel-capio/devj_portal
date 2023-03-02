<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;


class ServerRevisionSheet implements WithEvents, WithTitle, FromView, WithStyles, WithColumnWidths
{   
    use Exportable;
    protected $lastRow = 4;    //update when updating the file's revision history

    public function title(): string
    {   
        return 'Revision';
    }

    public function columnWidths(): array
    {
        return [
            'B' => 8,
            'C' => 16,
            'D' => 15,
            'E' => 32,
        ];
    }

    public function view(): View
    {
        return view('servers.serverMonitoringFileRevision');
    }

    public function styles(Worksheet $sheet)
    {
        return [
                'B2:E2' => [
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => (new Color('DAEEF3'))->getARGB()
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ], 
                ],
                'B2:D' .$this->lastRow => [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],       
                ],                
                'B2:E' .$this->lastRow => [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'argb' => Color::COLOR_BLACK
                            ],
                            'colorIndex' => [
                                'argb' => Color::COLOR_BLACK
                            ]
                        ]
                    ],
                ],

            ];
    }

    public function registerEvents(): array
    {   
        return [
                AfterSheet::class => function(AfterSheet $event) {
                    $event->sheet
                        ->getPageSetup()
                        ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

                    $event->sheet->setSelectedCell('A1');
                },
            ];
    }
}
