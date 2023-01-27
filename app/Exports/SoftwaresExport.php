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
    private $fileType;

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
        return [
            "A1:M1" => [    //style for header
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

        //get the number of type. to be used in computing the range
        // $prod_count = 0;
        // $message_count = 0;
        // $browser_count = 0;
        // $system_count = 0;
        // $p_specific = 0;
        // $p_drivers = 0;
        
        // $software = Softwares::whereIn('approved_status', [2])
        //         ->orderBy('type', 'ASC')
        //         ->orderBy('software_name', 'ASC');

        if($this->fileType == 'pdf'){
            $setting = [
                AfterSheet::class => function(AfterSheet $event) {
                    $event->sheet
                        ->getPageSetup()
                        ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE)
                        ->setPaperSizeDefault(WorksheetPageSetup::PAPERSIZE_A4);

                    //$event->sheet->getDelegate()->mergeCells('A1:A2');
                    },
            ];

        }else{
            $setting = [
                AfterSheet::class => function(AfterSheet $event) {
                        $event->sheet
                            ->getPageSetup()
                            ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE);
                        $event->sheet->getDelegate()->mergeCells('A1:A2');

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