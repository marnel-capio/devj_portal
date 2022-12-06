<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeesLaptops extends Model
{
    use HasFactory;

    protected $table = 'employees_laptops';
    protected $guarded = [];

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';

    static function getOwnedLaptopByEmployee($id){
        
        return self::selectRaw('laptops.id
                                ,laptops.tag_number
                                ,laptops.laptop_make
                                ,laptops.laptop_model
                                ,case when employees_laptops.brought_home_flag 
                                        then "Yes"
                                        else "No"
                                        end as brought_home
                                ,case when employees_laptops.vpn_flag
                                        then "Yes"
                                        else "No"
                                        end as use_vpn

                                ')
                    ->leftJoin('laptops', 'employees_laptops.laptop_id', 'laptops.id')
                    ->where('employees_laptops.employee_id', $id)
                    ->where('employees_laptops.surrender_flag', 0)
                    ->where('employees_laptops.approved_status', 2)
                    ->orderBy('laptops.tag_number', 'ASC')
                    ->get()
                    ->toArray();
    }

    static function getLinkageData($laptopId){
        return self::selectRaw('
									employees_laptops.id,
									employees_laptops.brought_home_flag,
									employees_laptops.vpn_flag,
									employees_laptops.approved_status,
									employees_laptops.remarks,
									CONCAT(employees.last_name, ", ", employees.first_name) AS employee_name
		')
		->where('laptop_id', $laptopId)
								->leftJoin('employees', 'employees.id', 'employees_laptops.employee_id')
                                ->where('employees_laptops.surrender_flag', 0)
                                ->whereIn('employees_laptops.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                ->orderBy('employees_laptops.update_time', 'desc')
								->first();

    }

    static function getLaptopHistory($id){
        return self::selectRaw('
		employees_laptops.id,
                                CONCAT(employees.last_name, ", ", employees.first_name) AS employee_name,
                                CASE WHEN employees_laptops.vpn_flag THEN "Y" ELSE "N" END AS vpn_flag,
                                CASE WHEN employees_laptops.brought_home_flag THEN "Y" ELSE "N" END AS brought_home_flag,
                                CASE WHEN isnull(employees_laptops.remarks) THEN "" ELSE employees_laptops.remarks END AS remarks,
                                CASE WHEN isnull(employees_laptops.surrender_flag) OR employees_laptops.surrender_flag THEN employees_laptops.surrender_date ELSE "" END AS surrender_date
                                ')
                        ->leftJoin('employees', 'employees.id', 'employees_laptops.employee_id')
						->where('employees_laptops.laptop_id', $id)
						->whereIn('employees_laptops.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
						->orderBy('employees_laptops.surrender_flag', 'asc')
						->orderBy('employees_laptops.surrender_date', 'asc')
						->get()
						->toArray();
    }
}
