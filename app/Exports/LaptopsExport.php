<?php

namespace App\Exports;

use App\Models\Employees;
use App\Models\Laptops;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
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
                        WithColumnWidths,
                        WithTitle
{
    use Exportable;

    protected $grayRows = [];
    protected $maxRow;
    protected $startRowForData = 3; //1st-2nd row - header
    protected $isPdf = "";
    protected $keyword;
    protected $availability;
    protected $status;
    protected $srchFilter;
    
    public function __construct($keyword = '', $availability = '', $status = '', $srchFilter = '', $isPdf = false)
    {
        $this->isPdf = $isPdf;
        $this->keyword = $keyword;
        $this->availability = $availability;
        $this->status = $status;
        $this->srchFilter = $srchFilter;
    }

    public function title(): string
    {
        return 'Laptop';
    }

    public function view(): View
    {
        $data = Laptops::getLaptopList($this->keyword, $this->availability, $this->status, $this->srchFilter, false);
        foreach($data as $idx => $item){
            if($item['surrender_flag'] || empty($item['linkage_id']) || empty($item['linked_employee_id']) 
                || (!empty($item['linked_employee_id']) && !$item['linked_employee_status'])){
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
                'E' => 10,
                'F' => 15,
                'G' => 15,
                'H' => 15,
                'I' => 15,
                'J' => 10,
                'K' => 10,
                'L' => 25,
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
                'I' => 15,
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
            "A1:M2" => [    //style for header
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
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

        if ( $this->maxRow >= $this->startRowForData ) {
            $style["A3:M" .$this->maxRow] = [
                                                'alignment' => [
                                                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                                                    'vertical' => Alignment::VERTICAL_CENTER,
                                                    'wrapText' => true
                                                ],
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
                                            ];
        }

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
        if($this->isPdf){
            $setting = [
                AfterSheet::class => function(AfterSheet $event) {
                    $event->sheet
                        ->getPageSetup()
                        ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE)
                        ->setPaperSizeDefault(WorksheetPageSetup::PAPERSIZE_LEGAL);
                    
                    $event->sheet->setSelectedCell('A1');
                },
            ];
        }else{
            $setting = [
                AfterSheet::class => function(AfterSheet $event) {
                    $event->sheet
                        ->getPageSetup()
                        ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE);
                    
                    $event->sheet->setSelectedCell('A1');
                },
            ];
        }
    
        return $setting;
    }
}
