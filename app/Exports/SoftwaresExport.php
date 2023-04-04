<?php
namespace App\Exports;

use App\Models\Softwares;
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


class SoftwaresExport implements FromView, WithEvents, WithColumnWidths, WithStyles, WithTitle
{
    use Exportable;

    private $fileType = '';
    protected $rowHeightArray = [];

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
                                            'detail_note' => $detail_note, 
                                            'file_type' => $this->fileType]
                                            );
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


    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 50,
        ];        
    }

    public function styles(Worksheet $sheet)
    {

        $data_count = Softwares::whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
        ->count();

       
        $bordered_cell = "A4:C" . (strVal($data_count) + config('constants.SOFTWARE_RANGE_BUFFER'));

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
            'A1:C2' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => StyleBorder::BORDER_THIN,
                        'color' => [
                            'argb' => Color::COLOR_WHITE
                        ],
                        'colorIndex' => [
                            'argb' => Color::COLOR_WHITE
                        ]
                    ]
                ],
            ],
            'A3:C3' => [
                'borders' => [
                    'vertical' => [
                        'borderStyle' => StyleBorder::BORDER_THIN,
                        'color' => [
                            'argb' => Color::COLOR_WHITE
                        ],
                        'colorIndex' => [
                            'argb' => Color::COLOR_WHITE
                        ]
                    ]
                ],
            ],
            
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

        $software = Softwares::getSoftwareForDownload();

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

        //Computation of new row size - start
        //get the new height based on merge cells
        $rowMultiplier = 1;
        $currentRow = config('constants.SOFTWARE_RANGE_BUFFER');
        $lineBreakCount = 0;
        $partitionCount = 0;
        $software_type_length = 0;
        $currentSoftwareTypeId = null;

        $calcRowHeight  = function ($partitionCount, $rowMultiplier, &$currentRow, $software_type_length, $software_Id) {
            //calculate the row height for the current software_type before proceeding to the next software_type    
            $rowHeight = 0;
            $software_type_line = ceil( ($software_type_length / config('constants.SOFTWARE_TYPE_MAX_CHAR_LINE')));
        
            if ($software_type_line > ($rowMultiplier * $partitionCount)) //multiplying rowmultiplier to partitioncount to get the total rows of all software that has the same software type
            {
                
                //if possible character line of software_type
                //is still greater than max total line count of each row that has the sanme software_type
                //calculate the new rowMultiplier by deviding the software_type_line by number of partition and rounding it up
                //to have an equal size of row for each software
                $rowMultiplier = ceil( ($software_type_line / $partitionCount));
                $rowHeight = ceil( ($rowMultiplier) * config('constants.DEFAULT_ROW_HEIGHT_VALUE'));

            }
            else if ($partitionCount >= $rowMultiplier ) {
                $rowHeight = config('constants.DEFAULT_ROW_HEIGHT_VALUE');
            } else {
                //calculate new row height
                $rowHeight = ceil( ($rowMultiplier/$partitionCount ) * config('constants.DEFAULT_ROW_HEIGHT_VALUE'));
            }

            //set value of row height for the current software_type
            for ($i = 0 ; $i < $partitionCount ; $i ++ ) { 
                $currentRow++;  //set current row 
                $this->rowHeightArray[$currentRow] = $rowHeight;
            }
        };

        $currentSoftwareId = 0;
        foreach ($software as $idx => &$software_data) {

            // This block of code is just a workaround to fix the row height of each row since textwrap is not working when there are merged cells in the file
            if ($currentSoftwareTypeId != $software_data['software_type_id']) {

                if (!is_null($currentSoftwareTypeId)) {
                    $calcRowHeight ($partitionCount, $rowMultiplier, $currentRow, $software_type_length, $currentSoftwareId);
                }

                //initialize variables for next server
                $currentSoftwareTypeId = $software_data['software_type_id'];
                $partitionCount = 0;
                $lineBreakCount = 0;
                $rowMultiplier = 1;
                $software_type_length = strlen($software_data['type']); //get the count of each software type
            }
            $currentSoftwareId = $software_data['id'];
            $partitionCount ++;

            //get the lines of each row if software name and remarks/purpose
            //by getting the total length of characters the value have and devide it
            //by number of characters each column can hold per line

            $software_name_length = strlen($software_data['software_name']);
            $remarks_length = strlen($software_data['remarks']);
            $software_name_lines = ceil( ($software_name_length / config('constants.SOFTWARE_NAME_MAX_CHAR_LINE')));
            $remarks_lines = ceil( ($remarks_length / config('constants.SOFTWARE_REMARKS_MAX_CHAR_LINE')));

            if ($software_name_lines > $lineBreakCount) 
            {
                $lineBreakCount = $software_name_lines;
            }
            if ($remarks_lines > $lineBreakCount) {
                $lineBreakCount = $remarks_lines;
            }
            if ($lineBreakCount > $rowMultiplier) {
                $rowMultiplier = $lineBreakCount;
            }

        }

        //calculate the final server
        $calcRowHeight ($partitionCount, $rowMultiplier, $currentRow, $software_type_length, $currentSoftwareId);
        //Computation of new row size = end
        
        if($this->fileType == config('constants.FILE_TYPE_PDF')){
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
                            $event->sheet->getDelegate()->getDefaultRowDimension()->setRowHeight(-1);

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

                            foreach ($this->rowHeightArray as $rowNumber => $rowHeight) {
                                $event->sheet->getRowDimension($rowNumber)->setRowHeight($rowHeight, config('constants.DEFAULT_ROW_HEIGHT_UNIT'));
                            }                    
        
                    },
            ];
        }


        return $setting;
    }

}