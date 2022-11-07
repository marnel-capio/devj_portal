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

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, WithEvents, ShouldAutoSize
{
    use Exportable;

    public function __construct($keyword,$filter,$status)
    {
        $this->keyword = $keyword;
        $this->filter = $filter;
        $this->status = $status;
    }

    public function headings(): array
    {
        return ["Date updated", "Full Name", "Cellphone Number", "Other Contact Details (if any)", "Current Address", "Permanent Address"];
    }	

    /**
    * @var Employees $employee
    */
    public function map($employee): array
    {	

        return [
            date("Y-m-d",strtotime($employee->update_time)),
            $employee->last_name.", ". $employee->first_name." (". $employee->middle_name.")",
            $employee->cellphone_number,
            $employee->other_contact_number,
            $employee->current_address_street. ", ". $employee->current_address_city. ", ". $employee->current_address_province . " " . $employee->current_address_postalcode,
            $employee->permanent_address_street. ", ". $employee->permanent_address_city. ", ". $employee->permanent_address_province . " " . $employee->permanent_address_postalcode,
        ];
    }	

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet
				    ->getPageSetup()
				    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            },
        ];
    }

    public function query()
    {	
        $keyword = $this->keyword;
        $status = $this->status;

        $employee = Employees::query()->where(function($query) {
                        $query->where('active_status', 0)
                        ->where('approved_status',2);
                    })
                    ->orWhere(function($query) {
                        $query->where('active_status', 1)
                        ->whereIn('approved_status',[2,4]);
                    });

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

        if ($status != 1) {
            $employeeStatus = $status == 2 ? 1 : 0;
            $employee = $employee->where('active_status', $employeeStatus);
        }

        $employee = $employee->orderBy('last_name', 'ASC');
        return $employee;
    }
}