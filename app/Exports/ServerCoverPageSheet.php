<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ServerCoverPageSheet implements WithEvents, WithTitle
{

    use Exportable;

    public function title(): string
    {
        return 'Cover Page';
    }

    public function registerEvents(): array
    {   
        return [
                AfterSheet::class => function(AfterSheet $event) {
                $event->sheet
                    ->getPageSetup()
                        ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

                $event->sheet->setCellvalue('B5', 'Capacity monitoring')
                                ->setCellValue('B6', 'v.2.0');
                $event->sheet->getStyle('B5')->getFont()->setSize(20)->setBold(true);

                $event->sheet->setSelectedCell('A1');
            },
        ];
    }
}
