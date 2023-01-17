<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EmployeesLaptops extends Model
{
    use HasFactory;

    protected $table = 'employees_laptops';
    protected $guarded = [];

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';

    /**
     * get owned laptops of employee
     *
     * @param [type] $id
     * @return array
     */
    static function getOwnedLaptopByEmployee($id){
        
        return self::selectRaw('laptops.id
                                ,employees_laptops.id as linkage_id
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
                    ->whereIn('employees_laptops.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                    ->orderBy('laptops.tag_number', 'ASC')
                    ->get()
                    ->toArray();
    }

    /**
     * get current laptop linkage data
     *
     * @param [type] $laptopId
     * @return void
     */
    static function getLinkageData($laptopId){
        return self::selectRaw('
									employees_laptops.id,
									employees_laptops.employee_id,
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

    /**
     * get laptop history
     *
     * @param [type] $id
     * @return array
     */
    static function getLaptopHistory($id){
        return self::selectRaw('
                                employees_laptops.id,
                                employees_laptops.surrender_flag,
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
                    ->orderBy('employees_laptops.surrender_date', 'desc')
                    ->get()
                    ->toArray();
    }

    /**
     * Get all link request for home screen 
     *
     * @return array
     */
    static function getLinkLaptopRequest(){
        $query = self::selectRaw('
                                employees.id as employee_id,
                                concat(employees.last_name, ", ", employees.first_name) as employee_name,
                                laptops.id as laptop_id,
                                laptops.tag_number,
                                laptops.laptop_make,
                                laptops.laptop_model,
                                employees_laptops.id,
                                employees_laptops.remarks,
                                case when employees_laptops.vpn_flag then "Y" else "N" end as vpn_access,
                                case when employees_laptops.brought_home_flag then "Y" else "N" end as brought_home,
                                employees_laptops.update_time as request_date 
                            ')
                        ->leftJoin('laptops', 'laptops.id', 'employees_laptops.laptop_id')
                        ->leftJoin('employees', 'employees.id', 'employees_laptops.employee_id')
                        ->whereIn('laptops.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                        ->where('laptops.status', 1)
                        ->whereIn('employees_laptops.approved_status', [config('constants.APPROVED_STATUS_PENDING'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                        ->whereIn('employees.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);

        if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE')){
            //get all laptop request and laptop linkage requests of the current user only
            $query->where('employees_laptops.employee_id', Auth::user()->id);
        }
        $query->orderBy('employees.last_name', 'asc');

        return $query->get()->toArray();
    }

    /**
     * get all link request of a laptop
     *
     * @return array
     */
    static function getLinkRequestByLaptop($laptopId, $approvedStatus = ""){
        $query =  self::selectRaw('
                                employees.id as employee_id,
                                concat(employees.last_name, ", ", employees.first_name) as employee_name,
                                laptops.id as laptop_id,
                                laptops.tag_number,
                                laptops.laptop_make,
                                laptops.laptop_model,
                                employees_laptops.id,
                                employees_laptops.surrender_date,
                                employees_laptops.remarks,
                                case when employees_laptops.vpn_flag then "Y" else "N" end as vpn_flag,
                                case when employees_laptops.brought_home_flag then "Y" else "N" end as brought_home_flag,
                                case when employees_laptops.surrender_flag then "Y" else "N" end as surrender_flag,
                                employees_laptops.update_time as request_date,
                                employees.first_name,
                                employees.email,
                                laptops.tag_number,
                                employees_laptops.update_data
                            ')
                        ->leftJoin('laptops', 'laptops.id', 'employees_laptops.laptop_id')
                        ->leftJoin('employees', 'employees.id', 'employees_laptops.employee_id')
                        ->whereIn('laptops.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                        ->where('laptops.status', 1)
                        ->where('employees_laptops.laptop_id', $laptopId)
                        ->whereIn('employees.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);


        if(empty($approvedStatus)){
            $query->whereIn('employees_laptops.approved_status', [config('constants.APPROVED_STATUS_PENDING'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);
        }else{
            $query->where('employees_laptops.approved_status', $approvedStatus);
        }

        if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE')){
            //get all laptop request and laptop linkage requests of the current user only
            $query->where('employees_laptops.employee_id', Auth::user()->id);
        }

        return $query->get()->toArray();
    }
}
