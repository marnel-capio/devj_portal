<?php
namespace App\Exports;

use App\Models\Softwares;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;   
use PhpOffice\PhpSpreadsheet\Style\Alignment;   
use PhpOffice\PhpSpreadsheet\Style\Color;   
use PhpOffice\PhpSpreadsheet\Style\Fill;   
use PhpOffice\PhpSpreadsheet\Style\Border as StyleBorder;   


class SoftwaresExport implements FromView,WithColumnWidths,WithStyles
{

    private $fileType = '';
    protected $rowHeightArray = [];

    public function view(): View
    {
         // get last approved
        $last_approved_software = Softwares::GetLastApproverDetail();
        $detail_note = '';
        $software = Softwares::getSoftwareForDownload();
        $softwareData = [];

        foreach ($software as $data) {
            $softwareData[$data['type']][] = ["name" => $data['software_name'], "remarks" => $data['remarks']];
        }

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

        return view('softwares/exports', [
            'softwareList' => $softwareData,
            'lastApproved' => $detail_note
        ]);
    }

    public function title(): string
    {
        return "Software List";
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 35, 
            'C' => 40,            
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $data_count = Softwares::whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])->count();

        $bordered_cell = "A3:C" . (strVal($data_count) + config('constants.SOFTWARE_RANGE_BUFFER'));

        return [
            // Style the first row as bold text.
            3    => [
                        'font' => [
                            'bold' => true
                        ],
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['argb' => "d6d4cb"],
                        ],
                    ],
            'A:C' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    // 'wrapText' => true,
                ],
            ],
            'B:C' => [
                'alignment' => [
                    'wrapText' => true,
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

}