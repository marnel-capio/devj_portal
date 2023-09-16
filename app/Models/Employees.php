<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;

class Employees extends Authenticatable
{
    use HasFactory;

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';

    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];
    
    /**
     * Get all employee requests
     *
     * @return void
     */
    static function getEmployeeRequest() {
    	$employee = Employees::select('id','first_name','last_name','email','position','approved_status','reasons')
                    ->where(function($query) {
                        $query->where('active_status', 0)
                            ->whereIN('approved_status', [1,3]);
                    })
                    ->orWhere('approved_status', 4)
    				->orderBy('last_name', 'ASC')
    				->get();

        return $employee;
    }
    
    /**
     * Get email of managers
     *
     * @return void
     */
    static function getEmailOfManagers(){
        return self::select('email')
                            ->where('roles', config('constants.MANAGER_ROLE_VALUE'))
                            ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                            ->where('active_status', 1)
                            ->get()
                            ->toArray();
    }

    /**
     * Get all the names of active employees for laptop linkage dropdown
     *
     * @return void
     */
    static function getEmployeeNameListForLaptopDropdown($laptopId){
        return self::selectRaw('id, CONCAT(last_name, ", ", first_name) AS employee_name')
                    ->where('active_status', 1)
                    ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                    ->whereNotIn('id', function($query) use ($laptopId){
                        $query->select('employee_id')
                                ->from('employees_laptops')
                                ->where('approved_status', config('constants.APPROVED_STATUS_PENDING'))
                                ->where('laptop_id', $laptopId);
                    })
                    ->orderBy('last_name', 'asc')
                    ->orderBy('first_name', 'asc')
                    ->get()
                    ->toArray();
    }

    /**
     * get all employee's laptop history
     *
     * @return array
     */
    static function getEmployeeLaptopHistory(){
        return self::selectRaw('
                            CONCAT(employees.last_name, ", ", employees.first_name) AS employee_name,
                            CASE WHEN employees_laptops.brought_home_flag THEN "Y" ELSE "N" END AS brought_home_flag,
                            laptops.peza_form_number,
                            laptops.peza_permit_number,
                            CASE WHEN employees_laptops.vpn_flag THEN "Y" ELSE "N" END AS vpn_access,
                            laptops.tag_number,
                            laptops.laptop_make,
                            laptops.laptop_model,
                            laptops.laptop_clock_speed,
                            laptops.laptop_cpu,
                            laptops.laptop_ram,
                            employees_laptops.remarks,
                            employees_laptops.update_time AS last_update,
                            employees_laptops.surrender_flag
                    ')
                    ->leftJoin('employees_laptops', 'employees_laptops.employee_id', 'employees.id')
                    ->leftJoin('laptops', 'employees_laptops.laptop_id', 'laptops.id')
                    ->where('employees.active_status', 1)
                    ->whereIn('employees.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                    ->where('laptops.status', 1)
                    ->whereIn('laptops.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                    ->whereIn('employees_laptops.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                    ->orderBy('employees.last_name', 'asc')
                    ->orderBy('employees.first_name', 'asc')
                    ->orderBy('laptops.tag_number', 'asc')
                    ->orderBy('employees_laptops.surrender_flag', 'asc')
                    ->orderBy('employees_laptops.surrender_date', 'asc')
                    ->orderBy('employees_laptops.created_by', 'desc')
                    ->get()
                    ->toArray();
    }

    /**
     * Get the active, approved, or employee with pending update.
     *
     * @param [type] $id
     * @return array
     */
    static function getActiveEmployeeDetails($id){
        return self::where('id', $id)
                ->where('active_status', 1)
                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                ->get()
                ->toArray();
    }

    

    /**
     * Get the passport status based on existing passport data
     *
     * @param [type] $employee
     * @return array
     */
    static function getPassportStatus($employee) {

        // set default value to true
        $employee->passport_isComplete = true;

        if($employee->passport_number   != null || 
        $employee->date_of_issue         != null || 
        $employee->issuing_authority     != null || 
        $employee->passport_type         != null || 
        $employee->passport_expiration_date != null || 
        $employee->place_of_issue        != null)
        {
            // If at least 1 field is not empty, then the passport exists.
            $employee->passport_status = 1;

            // If at least 1 field is empty, then passport details are incomplete.
            if($employee->passport_number   == null ||  
            $employee->date_of_issue         == null ||  
            $employee->issuing_authority     == null ||  
            $employee->passport_type         == null ||  
            $employee->passport_expiration_date == null ||  
            $employee->place_of_issue        == null)
            {
                $employee->passport_isComplete = false;
            }

        } else if($employee->date_of_appointment != null) {
            $employee->passport_status = 2;

        } else {
            $employee->passport_status = 3;

        }

        
        $exp_date = Carbon::now();
        if($employee->passport_status == 1){
            $exp_date = new Carbon($employee['passport_expiration_date']);
        }
        elseif($employee->passport_status == 2) {
            $exp_date = new Carbon($employee['date_of_appointment']);
        }

        $dur_years = Carbon::now()->diffInYears($exp_date);
        $dur_months = Carbon::now()->diffInMonths($exp_date);
        $dur_days = Carbon::now()->diffInDays($exp_date);

        $employee["passport_isWarning"] = true;
        if($dur_days <= 31) {
            $employee["duration"] = "$dur_days " . ($dur_days == 1 ?  "day" : "days");
        } else if($dur_months <= 12) {
            $employee["duration"] = "$dur_months " . ($dur_months == 1 ? "month" : "months");
        } else{
            if($dur_years == 1) {
                $employee["duration"] = "$dur_years year";
            } else {
                $employee["duration"] = "$dur_years years";
                $employee["passport_isWarning"] = false;
            }
        }

        return $employee;
    }

    
}
