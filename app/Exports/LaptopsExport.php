<?php

namespace App\Exports;

use App\Models\Employees;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as WorksheetPageSetup;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaptopsExport implements 
                        FromView,
                        WithEvents,
                        WithStyles,
                        ShouldAutoSize,
                        WithColumnWidths
{
    use Exportable;

    protected $grayRows = [];
    protected $maxRow;
    protected $startRowForData = 3; //1st-2nd row - header
    protected $isPdf = "";
    
    public function __construct($isPdf = false)
    {
        $this->isPdf = $isPdf;
    }

    public function view(): View
    {
        $data = Employees::getEmployeeLaptopHistory();
        foreach($data as $idx => $item){
            if($item['surrender_flag']){
                $this->grayRows[] = $idx + $this->startRowForData;
            }
        }
        $this->maxRow = $this->startRowForData + count($data) - 1; 

        return view('laptops.download')->with(['detail' => $data]);
    }

    public function columnWidths(): array
    {

        if($this->isPdf){
            $width = [
                'A' => 22,
                'B' => 10,
                'C' => 10,
                'D' => 10,
                'E' => 8,
                'F' => 10,
                'G' => 8,
                'H' => 8,
                'I' => 8,
                'J' => 8,
                'K' => 5,
                'L' => 18,
                'M' => 12,
            ];
        }else{
            $width = [
                'A' => 30,
                'B' => 20,
                'C' => 20,
                'D' => 20,
                'E' => 20,
                'F' => 25,
                'G' => 25,
                'H' => 25,
                'I' => 10,
                'J' => 10,
                'K' => 10,
                'L' => 30,
                'M' => 20,
            ];

        }
        return $width;
    }

    public function styles(Worksheet $sheet)
    {

        $style = [
            "A1:M3" => [    //style for header
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => (new Color('C0EEE4'))->getARGB(),
                        ]
                    ],
                ],
            "A3:M" .$this->maxRow =>[
              'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
            ],
            "A1:M" .$this->maxRow =>[
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
                ]
            ]
        ];

        foreach($this->grayRows as $idx => $value){
            $style["A" .$value .":M" .$value] = [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => (new Color('7F8487'))->getARGB(),
                    ]
                ],
            ];
        }

        if($this->isPdf){
            $sheet->getRowDimension('2')->setRowHeight(30);
            $sheet->getRowDimension('1')->setRowHeight(25);
        }

        return $style;
    }


    /**
     * @return array
     */
    public function registerEvents(): array
    {
        $setting = [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet
                    ->getPageSetup()
                    ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE);
            },
        ];
    
        return $setting;
    }
}
