<?php

namespace App\Exports;

use App\Models\Employees;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as WorksheetPageSetup;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaptopsExport implements 
                        // FromQuery,
                        // WithMapping,
                        FromView,
                        // WithHeadings, 
                        WithEvents,
                        WithStyles,
                        ShouldAutoSize,
                        WithColumnWidths
{
    use Exportable;

    protected $grayRows = [];

    protected $maxRow;

    protected $startRowForData = 4; //1st row: space, 2nd-3rd row - header
    
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
        return [
            'A' => 5,
        ];
    }


    // public function headings(): array
    // {
    //     return [
    //         [
    //             "Members",
    //             "Office PC brought home? (Y/N)",
    //             "PEZA",
    //             "",
    //             "VPN Access? (Y/N)",
    //             "Tag Number",
    //             "Laptop Make",
    //             "Model",
    //             "Clock Speed (GHz)",
    //             "RAM (GB)",
    //             "Remarks",
    //             "Last Updated",         
    //         ],
    //         [
    //             "",
    //             "",
    //             "Form Number",
    //             "Permit Number",
    //             "",
    //             "",
    //             "",
    //             "",
    //             "",
    //             "",
    //             "",
    //             "",      
    //         ]
    //     ];
    // }

    // public function query(){
    //     return Employees::selectRaw('
    //                 CONCAT(employees.last_name, ", ", employees.first_name) AS employee_name,
    //                 CASE WHEN employees_laptops.brought_home_flag THEN "Y" ELSE "N" END AS brought_home_flag,
    //                 laptops.peza_form_number,
    //                 laptops.peza_permit_number,
    //                 CASE WHEN employees_laptops.vpn_flag THEN "Y" ELSE "N" END AS vpn_access,
    //                 laptops.tag_number,
    //                 laptops.status,
    //                 laptops.laptop_make,
    //                 laptops.laptop_model,
    //                 laptops.laptop_clock_speed,
    //                 laptops.laptop_ram,
    //                 employees_laptops.remarks,
    //                 employees_laptops.update_time AS last_update,
    //                 employees_laptops.surrender_flag
    //                 ')
    //             ->leftJoin('employees_laptops', 'employees_laptops.employee_id', 'employees.id')
    //             ->leftJoin('laptops', 'employees_laptops.laptop_id', 'laptops.id')
    //             ->where('employees.active_status', 1)
    //             ->whereIn('employees.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
    //             ->where('laptops.status', 1)
    //             ->whereIn('employees_laptops.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
    //             ->orderBy('employees.last_name', 'asc')
    //             ->orderBy('employees.first_name', 'asc')
    //             ->orderBy('laptops.tag_number', 'asc')
    //             ->orderBy('employees_laptops.surrender_flag', 'asc')
    //             ->orderBy('employees_laptops.surrender_date', 'asc')
    //             ->orderBy('employees_laptops.created_by', 'desc')
    //             ->orderBy('laptops.tag_number', 'asc');
    // }


    // /**
    //  * Maps the query result in the excel file
    //  *
    //  * @param Employee $data
    //  * @return array
    //  */
    // public function map($data): array {
    //     return [
    //         $data->employee_name,
    //         $data->brought_home_flag,
    //         $data->peza_form_number,
    //         $data->peza_permit_number,
    //         $data->vpn_access,
    //         $data->tag_number,
    //         $data->laptop_make,
    //         $data->laptop_model,
    //         $data->laptop_clock_speed,
    //         $data->laptop_ram,
    //         $data->remarks,
    //         Date::dateTimeToExcel($data->last_update),
    //     ];
    // }

    public function styles(Worksheet $sheet)
    {

        $style = [
            "B2:M3" => [    //style for header
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => (new Color('C0EEE4'))->getARGB(),
                        ]
                    ],
                ],
            "B4:M" .$this->maxRow =>[
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            "B2:M" .$this->maxRow =>[
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
            $style["B" .$value .":M" .$value] = [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => (new Color('7F8487'))->getARGB(),
                    ]
                ],
            ];
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
