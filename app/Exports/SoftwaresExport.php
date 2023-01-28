<?php
namespace App\Exports;

//use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use App\Models\Softwares;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border as StyleBorder;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as WorksheetPageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoftwaresExport implements FromView, WithHeadings, WithMapping, WithEvents, ShouldAutoSize, WithColumnWidths, WithStyles, WithTitle
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
        $data = Softwares::whereIn('approved_status', [2])
                            ->orderBy('type', 'ASC')
                            ->orderBy('software_name', 'ASC')
                            ->get()->toArray();

       
 /*       foreach($data as $idx => $item){
            if($item['surrender_flag']){
                $this->grayRows[] = $idx + $this->startRowForData;
            }
        }
        $this->maxRow = $this->startRowForData + count($data) - 1; 
*/
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

        $data_count = Softwares::whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED')])
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
    * @var Softwares $software
    */
    public function map($software): array
    {	

        return [
            $software->type,
            $software->software_name,
            $software->remarks,
        ];
    }	

    /**
     * @return array
     */
    public function registerEvents(): array
    {

        
        //Merge cell processing  - start

        $software = Softwares::whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED')])
                 ->orderBy('type', 'ASC')
                 ->orderBy('software_name', 'ASC')
                 ->get()->toArray();

        $type_count = array(0,0,0,0,0,0);
        //check how many of each type are there in the array
        foreach($software as $value){
            $type_count[$value['type'] - config('constants.SOFTWARE_RANGE_BUFFER')] =  $type_count[$value['type'] - config('constants.SOFTWARE_RANGE_BUFFER')] + config('constants.SOFTWARE_TYPE_COUNTER_INCREMENT');      
        }


        //get 
        $prev_index = config('constants.SOFTWARE_RANGE_INITIAL_VALUE');

        $type_range = array('','','','','',''); 
        for ($x = 0; $x < config('constants.SOFTWARE_TYPE_COUNT'); $x++) {
            if($type_count[$x] != config('constants.SOFTWARE_TYPE_EMPTY'))
            {
                $type_range[$x] = 'A' . strval($prev_index) . ':A' . strval($prev_index + $type_count[$x] - config('constants.SOFTWARE_RANGE_BUFFER'));
                $prev_index = $prev_index + $type_count[$x];
            }

        }


        if($this->fileType == 'pdf'){
            $setting = [
                AfterSheet::class => function(AfterSheet $event) use($type_range) {
                    $event->sheet
                        ->getPageSetup()
                        ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE)
                        ->setPaperSizeDefault(WorksheetPageSetup::PAPERSIZE_A4);
                        for ($x = 0; $x < config('constants.SOFTWARE_TYPE_COUNT'); $x++) {
                            if($type_range[$x] != "")
                            {
                                $event->sheet->getDelegate()->mergeCells($type_range[$x]);
                            }
                        }

                    },
            ];

        }else{
            $setting = [
                AfterSheet::class => function(AfterSheet $event) use($type_range) {
                        $event->sheet
                            ->getPageSetup()
                            ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE)
                            ->setPaperSizeDefault(WorksheetPageSetup::PAPERSIZE_A4);
                            for ($x = 0; $x < config('constants.SOFTWARE_TYPE_COUNT'); $x++) {
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

/*    public function query()
    {	
        $keyword = $this->keyword;
        $status = $this->status;

        $software = Softwares::whereIn('approved_status', [2]);

        if (!empty($keyword)) {
            $software = $software->where(function($query) use ($keyword) {
                    $query->where('software_name','LIKE','%'.$keyword.'%');
                });
        }

        $software = $software
                            ->orderBy('type', 'ASC')
                            ->orderBy('software_name', 'ASC');

         //change the type into string
        foreach($software as $value){
            if($value['type'] == config('constants.SOFTWARE_TYPE_1'))
            { 
                $value['type'] = config('constants.SOFTWARE_TYPE_1_NAME');
            }
            else if($value['type'] == config('constants.SOFTWARE_TYPE_2'))
            { 
                $value['type'] = config('constants.SOFTWARE_TYPE_2_NAME');
            }
            else if($value['type'] == config('constants.SOFTWARE_TYPE_3'))
            { 
                $value['type'] = config('constants.SOFTWARE_TYPE_3_NAME');
            }
            else if($value['type'] == config('constants.SOFTWARE_TYPE_4'))
            { 
                $value['type'] = config('constants.SOFTWARE_TYPE_4_NAME');
            }
            else if($value['type'] == config('constants.SOFTWARE_TYPE_5'))
            { 
                $value['type'] = config('constants.SOFTWARE_TYPE_5_NAME');
            }
            else if($value['type'] == config('constants.SOFTWARE_TYPE_6'))
            { 
                $value['type'] = config('constants.SOFTWARE_TYPE_6_NAME');
            }
        }
        return $software;
    }
    */


}