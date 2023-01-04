<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
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
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as WorksheetPageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoftwaresExport implements FromQuery, WithHeadings, WithMapping, WithEvents, ShouldAutoSize, WithColumnWidths, WithStyles, WithTitle
{
    use Exportable;

    private $maxRow = 100;

    public function __construct($keyword,$status,$fileType = "")
    {
        $this->keyword = $keyword;
        $this->status = $status;
        $this->fileType = $fileType;
    }

    public function title(): string
    {
        return "Contact Info";
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
            $software->remarks,0
        ];
    }	

    /**
     * @return array
     */
    public function registerEvents(): array
    {


        if($this->fileType == 'pdf'){
            $setting = [
                AfterSheet::class => function(AfterSheet $event) {
                    $event->sheet
                        ->getPageSetup()
                        ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE)
                        ->setPaperSizeDefault(WorksheetPageSetup::PAPERSIZE_A4);
                },
            ];

        }else{
            $setting = [
                AfterSheet::class => function(AfterSheet $event) {
                    $event->sheet
                        ->getPageSetup()
                        ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE);
                },
            ];
        }


        return $setting;
    }

    public function query()
    {	
        $keyword = $this->keyword;
        $status = $this->status;

        $software = Softwares::whereIn('approved_status', [2,4]);

        if (!empty($keyword)) {
            $software = $software->where(function($query) use ($keyword) {
                    $query->where('software_name','LIKE','%'.$keyword.'%');
                });
        }

        $software = $software->orderBy('software_name', 'ASC');
        return $software;
    }
}