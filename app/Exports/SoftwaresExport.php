<?php
namespace App\Exports;

//use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use App\Models\Softwares;
use App\Models\SoftwareTypes;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border as StyleBorder;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as WorksheetPageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class SoftwaresExport implements FromView, WithHeadings, WithEvents, ShouldAutoSize, WithColumnWidths, WithStyles, WithTitle
{
    use Exportable;

    private $maxRow = 100;
    private $fileType = '';

    public function __construct($fileType = "")
    {
        $this->fileType = $fileType;
    }

    public function title(): string
    {
        return "Software List";
    }

    public function view(): View
    {
        $data = Softwares::getSoftwareForDownload();

        return view('softwares.download')->with(['detail' => $data]);
    }

    public function headings(): array
    {
        return ["Type", "Application Software", "Remarks"];
    }	

    public function columnWidths(): array
    {
        if($this->fileType == 'pdf'){
            return [
                'A' => 20,
                'B' => 25,
                'C' => 50,
            ];
        }else{
            return [];
        }
    }

    public function styles(Worksheet $sheet)
    {

        $data_count = Softwares::whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
        ->count();

        $bordered_cell = "A1:C" . (strVal($data_count) + config('constants.SOFTWARE_RANGE_BUFFER'));

        return [
            "A1:C1" => [    //style for header
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'color' => Color::COLOR_CYAN
            ],
            'A:C' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ],

            1 => ['font' => [
                'bold' => true
            ]],
            $bordered_cell =>[
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => StyleBorder::BORDER_THIN,
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
    }


    /**
     * @return array
     */
    public function registerEvents(): array
    {

        
        //Merge cell processing  - start

        $software = Softwares::whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                 ->orderBy('software_type_id', 'ASC')
                 ->orderBy('software_name', 'ASC')
                 ->get()->toArray();
        


        $types_count = array();
        //check how many of each type are there in the array
        foreach($software as $value)
        {
            if(!isset($types_count[$value['software_type_id']]))
            {
                $types_count[$value['software_type_id']] = 1;
            }
            else
            {
                $types_count[$value['software_type_id']] =  $types_count[$value['software_type_id']] + config('constants.SOFTWARE_TYPE_COUNTER_INCREMENT');      
            }
        }


        //get 
        $prev_excel_index = config('constants.SOFTWARE_RANGE_INITIAL_VALUE');

        $total_type_count = count($types_count);

        $range_index = 0;
        foreach($types_count as $type_count)
        {

            if($type_count != config('constants.SOFTWARE_TYPE_EMPTY'))
            {
                $type_range[$range_index] = 'A' . strval($prev_excel_index) . ':A' . strval($prev_excel_index + $type_count - config('constants.SOFTWARE_RANGE_BUFFER'));
                $prev_excel_index = $prev_excel_index + $type_count;
                $range_index++;
            }
            
        }

        if($this->fileType == 'pdf'){
            $setting = [
                AfterSheet::class => function(AfterSheet $event) use($type_range, $total_type_count) {
                    $event->sheet
                        ->setSelectedCell('A1')
                        ->getPageSetup()
                        ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE)
                        ->setPaperSizeDefault(WorksheetPageSetup::PAPERSIZE_LEGAL);
                        for ($x = 0; $x < $total_type_count; $x++) {
                            if($type_range[$x] != "")
                            {
                                $event->sheet->getDelegate()->mergeCells($type_range[$x]);
                            }
                        }

                    },
            ];

        }else{
            $setting = [
                AfterSheet::class => function(AfterSheet $event) use($type_range, $total_type_count) {
                        $event->sheet
                            ->setSelectedCell('A1')
                            ->getPageSetup()
                            ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE)
                            ->setPaperSizeDefault(WorksheetPageSetup::PAPERSIZE_A4);
                            for ($x = 0; $x < $total_type_count; $x++) {
                                if($type_range[$x] != "")
                                {
                                    $event->sheet->getDelegate()->mergeCells($type_range[$x]);
                                }
                            }

                    },
            ];
        }


        return $setting;
    }

}