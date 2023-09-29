<?php
namespace App\Exports;

use App\Models\Softwares;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border as StyleBorder;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as WorksheetPageSetup;

class SoftwaresExport implements FromView,WithColumnWidths,WithStyles
{
    use Exportable;
    private $rowCountPerType = [];
    
    public function view(): View
    {
         // get last approved
        $last_approved_software = Softwares::GetLastApproverDetail();
        $detail_note = '';
        $software = Softwares::getSoftwareForDownload();
        $softwareData = [];
        $ctr = 1;
        $type = "";
        foreach ($software as $data) {
            if ($type != $data['type']) {
                $this->rowCountPerType[] = $ctr;
                $type = $data['type'];
            }
            $softwareData[$data['type']][] = ["name" => $data['software_name'], "remarks" => $data['remarks']];
            $ctr = $ctr+1;
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

        $bordered_cell = "B3:C" . (strVal($data_count) + config('constants.SOFTWARE_RANGE_BUFFER'));
        $borderTopLeft = [];
        $borderLeft = [];
        $borderBottom = "A". (strVal($data_count) + config('constants.SOFTWARE_RANGE_BUFFER'));
        for ($i = 0 ; $i < count($this->rowCountPerType) ; $i++) {
            $rowCount = $this->rowCountPerType[$i] + config('constants.SOFTWARE_RANGE_BUFFER');
            $endRowCountType = ($i+1 != count($this->rowCountPerType)) ? $this->rowCountPerType[$i+1] + config('constants.SOFTWARE_RANGE_BUFFER') : strVal($data_count) + config('constants.SOFTWARE_RANGE_BUFFER');
            $borderTopLeft[] = "A".strVal($rowCount);
            $borderLeft[] = "A".strVal($rowCount).":A".strVal($endRowCountType);
        }

        $styles = [
            // Style the first row as bold text.
            'A:C' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'font' => ['size' => 12]
                    // 'wrapText' => true,
                ],
            ],
            'B:C' => [
                'alignment' => [
                    'wrapText' => true,
                ],
            ],
            'A3:C3' => [
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
                ],
                'font' => [
                    'bold' => true
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => "d6d4cb"],
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
            ],
            $borderBottom =>[
                'borders' => [
                    'bottom' => [
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
        $appendStyle = [];
        //border for top
        foreach ($borderTopLeft as $key => $value) {
           $appendStyle[$value] =  [
                                    'borders' => [
                                        'top' => [
                                            'borderStyle' => StyleBorder::BORDER_THIN,
                                            'color' => [
                                                'argb' => Color::COLOR_BLACK
                                            ],
                                            'colorIndex' => [
                                                'argb' => Color::COLOR_BLACK
                                            ]
                                        ]
                                    ]
                                 ];
        }

        //border for left
        foreach ($borderLeft as $key => $value) {
            $appendStyle[$value] =  [
                                    'borders' => [
                                        'left' => [
                                            'borderStyle' => StyleBorder::BORDER_THIN,
                                            'color' => [
                                                'argb' => Color::COLOR_BLACK
                                            ],
                                            'colorIndex' => [
                                                'argb' => Color::COLOR_BLACK
                                            ]
                                        ],
                                    ]
                                 ];
        }

        $styles = array_merge($appendStyle,$styles);
        return $styles;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        $setting = [
            AfterSheet::class => function(AfterSheet $event) use($type_range, $total_type_count) {
                $event->sheet
                    ->setSelectedCell('A1')
                    ->getPageSetup()
                    // ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSizeDefault(WorksheetPageSetup::PAPERSIZE_LEGAL);
    
                },
        ];


        return $setting;
    }

}