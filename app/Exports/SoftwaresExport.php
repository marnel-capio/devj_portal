<?php
namespace App\Exports;

//use Maatwebsite\Excel\Concerns\FromQuery;
use App\Models\Softwares;
use App\Models\SoftwareTypes;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;

use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border as StyleBorder;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as WorksheetPageSetup;


class SoftwaresExport implements FromView, WithHeadings, WithEvents, WithColumnWidths, WithStyles, WithTitle
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

        //get latest approver and latest approve time
        $detail_note = $this->getLastApproveNote();
        

        return view('softwares.download',['detail' => $data,
                                            'detail_note' => $detail_note]);
    }
    public function getLastApproveNote()
    {
        $last_approved_software = Softwares::GetLastApproverDetail();
        $detail_note = '';

    
        if($last_approved_software)
        {
            if($last_approved_software->approver){
                $detail_note = 'Last approved by: ' . $last_approved_software->approver;
            }
            
            if($last_approved_software->approve_time)
            {
                $current_date = date("Y-m-d", strtotime($last_approved_software->approve_time) );
                $detail_note = $detail_note . ' as of ' . $current_date;
            }
        }

        return $detail_note;
    }


    public function headings(): array
    {
        return ["Type", "Application Software", "Remarks"];
    }	

    public function columnWidths(): array
    {
        if($this->fileType == 'pdf'){
            $width = [
                'A' => 20,
                'B' => 10,
                'C' => 15,
            ];
        }else{
            $width = [
                'A' => 20,
                'B' => 25,
                'C' => 50,
            ];
        }
        return $width;        
    }

    public function styles(Worksheet $sheet)
    {

        $data_count = Softwares::whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
        ->count();

       
        $bordered_cell = "A4:C" . (strVal($data_count) + config('constants.SOFTWARE_RANGE_BUFFER'));

       // dd($bordered_cell);

        return [
            "A4:C4" => [    //style for header
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => Color::COLOR_YELLOW,
                    ]
                ],
        ],
            'A:C' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'wrapText' => true,
                ],
            ],
            'A2:C2' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'wrapText' => false,
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
                $type_range[$range_index] = 'A' . strval($prev_excel_index) . ':A' . strval($prev_excel_index + $type_count - config('constants.SOFTWARE_RANGE_HEADER_BUFFER'));
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