<?php

namespace App\Exports;

use App\Models\Servers;
use FFI;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as WorksheetPageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\Wizard;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;

class ServerDataSheet implements
                        FromView,
                        WithEvents,
                        WithStyles,
                        WithColumnWidths,
                        ShouldAutoSize,
                        WithTitle
                        
{
    use Exportable;
    protected $maxRow = 7;

    public function __construct()
    {

    }

    public function view(): View
    {
        $downloadData = Servers::selectRaw('
                                    s.id,
                                    s.server_name,
                                    s.server_ip,
                                    s.function_role,
                                    s.os,
                                    s.cpu,
                                    s.motherboard,
                                    s.memory,
                                    s.hdd,
                                    s.memory_used_size,
                                    s.memory_used_size_type,
                                    s.memory_used_percentage,
                                    s.memory_free_size,
                                    s.memory_free_size_type,
                                    s.memory_free_percentage,
                                    s.memory_total,
                                    s.memory_total_size_type,
                                    s.os_type,
                                    s.other_os_percentage,
                                    s.hdd_status,
                                    s.ram_status,
                                    s.cpu_status,
                                    s.remarks,
                                    s.status,
                                    s.updated_by,
                                    s.linux_us_percentage,
                                    s.linux_ni_percentage,
                                    s.linux_sy_percentage,
                                    sp.hdd_partition,
                                    sp.hdd_used_size,
                                    sp.hdd_used_size_type,
                                    sp.hdd_used_percentage,
                                    sp.hdd_free_size,
                                    sp.hdd_free_size_type,
                                    sp.hdd_free_percentage,
                                    sp.hdd_total,
                                    sp.hdd_total_size_type,
                                    sp.server_id,
                                    CONCAT(e.first_name, " ", e.last_name) AS updater
                            ')
                        ->from('servers as s')
                        ->leftJoin('servers_partitions as sp', 's.id', 'sp.server_id')
                        ->leftjoin('employees as e', 'e.id', 's.updated_by')
                        ->orderBy('s.id', 'asc')
                        ->orderBy('sp.id', 'asc')
                        ->get()
                        ->toArray();

        $this->maxRow += count($downloadData);

        $serverData = [];
        foreach ($downloadData as $idx => &$data) {
            //convert values
            //convert the memory size
            $this->convertSizeToGigaBytes($data['memory_used_size'], $data['memory_used_size_type']);
            $this->convertSizeToGigaBytes($data['memory_free_size'], $data['memory_free_size_type']);
            $this->convertSizeToGigaBytes($data['memory_total'], $data['memory_total_size_type']);

            //convert values of each hdd partition
            $this->convertSizeToGigaBytes($data['hdd_used_size'], $data['hdd_used_size_type']);
            $this->convertSizeToGigaBytes($data['hdd_free_size'], $data['hdd_free_size_type']);
            $this->convertSizeToGigaBytes($data['hdd_total'], $data['hdd_total_size_type']);  

            $serverData[$data['id']][] = $data;
        }

        return view('servers.download', ['serverData' => $serverData]);
    }

    /**
     * Converts any unit to GB, 
     * rounds to 2 decimal places
     *
     * @param [type] $size
     * @param [type] $origUnit
     */
    private function convertSizeToGigaBytes (&$size, $origUnit) {
        $size = $size * pow(config('constants.KB_TO_BYTES'), $origUnit - 4); //since 1GB = 1024^4 B, 4 is the index or the base
        $size =  round($size, 2);
    }

    public function title(): string
    {
        return date('F Y');
    }

    public function columnWidths(): array
    {
        return [
            'B' => 26,
            'C' => 34,
            'D' => 61,
            'E' => 15,
            'F' => 10,
            'G' => 10,
            'H' => 10,
            'I' => 10,
            'J' => 10,
            'K' => 10,
            'L' => 10,
            'M' => 10,
            'N' => 10,
            'O' => 10,
            'P' => 10,
            'Q' => 10,
            'R' => 10,
            'S' => 10,
            'T' => 15,
            'U' => 15,
            'V' => 15,
            'W' => 15,
            'X' => 21,
            'Y' => 30,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $style = [
                'B4:Y7' => [
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],            
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => (new Color('8DB3E2'))->getARGB(),
                        ]
                    ],
                ],
                'B8:Y' .$this->maxRow => [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                ],
                'F8:W' .$this->maxRow => [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                ],
                'B4:Y' .$this->maxRow => [
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
                'B' .($this->maxRow + 4) => [   //legend title
                    'font' => ['bold' => true]
                ],
                'B' .($this->maxRow + 6) => [   //Stable
                    'font' => [
                        'color' => [
                            'argb' => (new Color('0066CC'))->getARGB(),
                        ]
                    ]
                ],
                'B' .($this->maxRow + 7) => [   //Critical
                    'font' => [
                        'color' => [
                            'argb' => Color::COLOR_RED,
                        ]
                    ]
                ],
            ];

        return $style;

    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet
                    ->getPageSetup()
                        ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE);

                //conditional formatting
                $stableCondition = new Conditional();
                $stableCondition->setConditionType(Conditional::CONDITION_CONTAINSTEXT)
                                    ->setOperatorType(Conditional::OPERATOR_CONTAINSTEXT)
                                    ->setText('Stable')
                                    ->getStyle()->getFont()->getColor()->setARGB((new Color('0066CC'))->getARGB());
                $criticalCondition = new Conditional();
                $criticalCondition->setConditionType(Conditional::CONDITION_CONTAINSTEXT)
                                    ->setOperatorType(Conditional::OPERATOR_CONTAINSTEXT)
                                    ->setText('Critical')
                                    ->getStyle()->getFont()->getColor()->setARGB(Color::COLOR_RED);
                
                $event->sheet->setSelectedCell('A1');
            },
        ];
    }

}
