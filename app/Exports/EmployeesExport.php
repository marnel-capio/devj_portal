<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use App\Models\Employees;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as WorksheetPageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, WithEvents, ShouldAutoSize, WithColumnWidths, WithStyles, WithTitle
{
    use Exportable;

    private $maxRow = 100;
    private $keyword;
    private $filter;
    private $status;
    private $fileType;

    public function __construct($keyword,$filter,$status,$passport_status,$fileType = "")
    {
        $this->keyword = $keyword;
        $this->filter = $filter;
        $this->status = $status;
        $this->fileType = $fileType;

        switch($passport_status) {
            case 2 :
                // With Passport
                $this->passport_status = 1;
                break;
            case 3 :
                // Waiting Delivery
                $this->passport_status = 4;
                break;
            case 4 :
                // With Appointment
                $this->passport_status = 2;
                break;
            case 5 :
                // No appointment
                $this->passport_status = 3;
                break;
            default:
                $this->passport_status = 0;
                break;
        }
    }

    public function title(): string
    {
        return "Contact Info";
    }

    public function headings(): array
    {
        return ["Date updated", "Full Name", "BU Assignment", "Cellphone Number", "Other Contact Details (if any)", "Current Address", "Permanent Address"];
    }	

    public function columnWidths(): array
    {
        if($this->fileType == 'pdf'){
            return [
                'A' => 12,
                'B' => 25,
                'C' => 15,
                'D' => 15,
                'E' => 15,
                'F' => 40,
                'G' => 40,
            ];
        }else{
            return [];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [

            'A:F' => [
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
    * @var Employees $employee
    */
    public function map($employee): array
    {	

        return [
            date("Y-m-d",strtotime($employee->update_time)),
            $employee->last_name.", ". $employee->first_name." (". $employee->middle_name.")",
            $employee->bu_transfer_flag ? config('constants.BU_LIST.' .$employee->bu_transfer_assignment) : "",
            $employee->cellphone_number,
            $employee->other_contact_info,
            $employee->current_address_street. ", ". $employee->current_address_city. ", ". $employee->current_address_province . " " . $employee->current_address_postalcode,
            $employee->permanent_address_street. ", ". $employee->permanent_address_city. ", ". $employee->permanent_address_province . " " . $employee->permanent_address_postalcode,
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
                        ->setSelectedCell('A1')
                        ->getPageSetup()
                        ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE)
                        ->setPaperSizeDefault(WorksheetPageSetup::PAPERSIZE_LEGAL);
                },
            ];

        }else{
            $setting = [
                AfterSheet::class => function(AfterSheet $event) {
                    $event->sheet
                        ->setSelectedCell('A1')
                        ->getPageSetup()
                        ->setOrientation(WorksheetPageSetup::ORIENTATION_LANDSCAPE);
                    $event->sheet->setSelectedCell('A1');
                },
            ];
        }


        return $setting;
    }

    public function query()
    {	
        $keyword = $this->keyword;
        $status = $this->status;
        $passport_status = $this->passport_status;

        $employee = Employees::whereIn('approved_status',  [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);

        if (!empty($keyword)) {

        	if ($this->filter == 1) {

                $employee = $employee->where(function($query) use ($keyword) {
                        $query->where('first_name','LIKE','%'.$keyword.'%')
                                ->orWhere('last_name','LIKE','%'.$keyword.'%')
                                ->orWhere('middle_name','LIKE','%'.$keyword.'%');
                    });

            } else if ($this->filter == 2) {

                $employee = $employee->where('current_address_city','LIKE','%'.$keyword.'%');

            } else if ($this->filter == 3) {

                $employee = $employee->where('current_address_province','LIKE','%'.$keyword.'%');

            }
        }

        if (Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            if ($status != 1) {
                if ($status != 1) {
                    if ($status == 4) {
                        $employee = $employee->where('bu_transfer_flag', 1);
                    }else{
                        $employeeStatus = $status == 2 ? 1 : 0;
                        $employee = $employee->where('active_status', $employeeStatus);
                    }
                }
            }
        }else{
            $employee = $employee->where('active_status', 1);
        }

        if($passport_status >= 1 && $passport_status <= 4) {
            $employee = $employee->where('passport_status', $passport_status);
        }

        $employee = $employee->orderBy('last_name', 'ASC');
        return $employee;
    }
}