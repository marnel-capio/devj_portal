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

        if($employee->passport_status == null || $employee->passport_status == "") {
            if($employee->passport_number   != null || 
            $employee->date_of_issue         != null || 
            $employee->issuing_authority     != null || 
            $employee->passport_type         != null || 
            $employee->passport_expiration_date != null || 
            $employee->place_of_issue        != null)
            {
                // If at least 1 field is not empty, then the passport exists.
                $employee->passport_status = 1;
    
            } else if($employee->date_of_appointment != null) {
                $employee->passport_status = 2;
    
            } else {
                $employee->passport_status = 3;
    
            }
        }

        
        // Initialize $today and $exp_date variables
        $exp_date = $today = Carbon::now();

        if($employee->passport_status == 1){
            $exp_date = new Carbon($employee['passport_expiration_date']);
        }
        elseif($employee->passport_status == 2) {
            $exp_date = new Carbon($employee['date_of_appointment']);
        }
        elseif($employee->passport_status == 4) {
            $exp_date = new Carbon($employee['date_of_delivery']);
        }

        // Determine the time difference for duration
        $duration_years = Carbon::now()->diffInYears($exp_date);
        $duration_months = Carbon::now()->diffInMonths($exp_date);
        $duration_days = Carbon::now()->diffInDays($exp_date);
        
        // Determine if date is in the future from today
        $employee["is_date_passed"] = $exp_date->lt($today);

        // Determine if Passport alert is warning(red) or info(blue)
        $employee["passport_isWarning"] = true;

        // Determine if Passport alert is to be displayed
        $employee["passport_isAlertDisplayed"] = true;


        if($duration_days <= 31) {
            $employee["duration"] = $duration_days;
            $employee["duration_unit"] = $duration_days > 1 ?  "days" : "day";

            // If status is for appointment or delivery, set warning only if day 0 - 7 
            $employee["passport_isWarning"] = (($employee->passport_status == 2 || $employee->passport_status == 4) && $duration_days > 6) ? false : true;

            $employee['duration_days'] = $employee['duration'] + 1;

        } else if($duration_months <= 12) {
            $employee["duration"] = $duration_months;
            $employee["duration_unit"] = $duration_months == 1 ? "month" : "months";

            // If status is for appointment, do not set warning
            $employee["passport_isWarning"] = ($employee->passport_status == 2) ? false : true;

            // If duration is > 3 months from now, do not display alert
            $employee["passport_isAlertDisplayed"] = ($duration_months > 2 && !$employee["is_date_passed"]) ? false : true;


        } else{
            $employee["passport_isAlertDisplayed"] = false;

            $employee["duration"] = $duration_years;
            if($duration_years == 1) {
                $employee["duration_unit"] = "year";
            } else {
                $employee["duration_unit"] = "years";
                $employee["passport_isWarning"] = false;
            }
        }

        
        // If date is already passed, automatically set Warning
        if($employee["is_date_passed"]) {
            $employee["passport_isWarning"] = true;
        }

        // check if user is devj-portal do not display passport warning
        if ($employee['email'] == config('constants.SYSTEM_EMAIL')) {
            $employee["passport_isAlertDisplayed"] = false;
        }

        $employee["passport_message"] = Employees::getPassportMessage($employee);

        return $employee;
    }

    

    /**
     * Get the passport status based on existing passport data
     *
     * @param [type] $employee
     * @return string
     */
    static function getPassportMessage($employee) {
        $passport_message = "null";
                
        if($employee['passport_status'] == 3)  {
            $passport_message = "Please consider setting a passport appointment as soon as possible.";

        }
        // For appointment, date is tomorrow onwards
        elseif($employee['passport_status'] == 2 && !$employee['is_date_passed']){
            $passport_message = "Passport appointment is in " . (isset($employee['duration_days']) ? $employee['duration_days'] : $employee['duration']) . " " . $employee['duration_unit'];

        }
        // For appointment, date is now or passed already
        elseif($employee['passport_status'] == 2 && $employee['is_date_passed']){
            if($employee['duration'] == 0 && str_contains($employee['duration_unit'], "day")) {
                $passport_message = "Passport appointment is today!";
            }
            else {
                $passport_message = "Passport appointment was " . $employee['duration'] . " " . $employee['duration_unit'] . " ago!";
            }

        }
        // With passport, date is tomorrow onwards
        elseif($employee['passport_status'] == 1 && !$employee['is_date_passed']) {
            $passport_message = "Passport expires in " . (isset($employee['duration_days']) ? $employee['duration_days'] : $employee['duration']) . " " . $employee['duration_unit'];

        }
        // With passport, expired
        elseif($employee['passport_status'] == 1 && $employee['is_date_passed']) {
            $passport_message = "Passport has expired!";

        }
        // For delivery, date is tomorrow onwards
        elseif($employee['passport_status'] == 4 && !$employee['is_date_passed']){
            $passport_message = "Passport delivery is in  " . (isset($employee['duration_days']) ? $employee['duration_days'] : $employee['duration']) . " " . $employee['duration_unit'];

        }
        // For delivery, date is passed
        elseif($employee['passport_status'] == 4 && $employee['is_date_passed']){
            if($employee['duration'] == 0 && str_contains($employee['duration_unit'], "day")) {
                $passport_message = "Passport delivery is today!";
            }
            else {
                $passport_message = "Passport delivery was " . $employee['duration'] . " " . $employee['duration_unit'] . " ago!";
            }

        }

        return $passport_message;
    }

    
}
