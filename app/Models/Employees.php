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
    	$employee = Employees::select('id','first_name','last_name', 'name_suffix', 'email','position','approved_status','reasons')
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
        return self::selectRaw('id, CONCAT(last_name, ", ", first_name, " ") AS employee_name, last_name, first_name, name_suffix')
                    ->where('active_status', 1)
                    ->where('email',"!=", config('constants.SYSTEM_EMAIL'))
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
                            employees.name_suffix name_suffix,
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

        $employee = Employees::getPassportDuration($employee);

        $employee["passport_message"] = Employees::getPassportMessage($employee);
        return $employee;
    }

    /**
     * Get the passport duration based on existing passport data
     *
     * @param [type] $employee
     * @return array
     */
    static function getPassportDuration($employee) {
        
        // Initialize $today and $exp_date variables
        $exp_date = $today = now()->startOfDay();

        if($employee->passport_status == config('constants.PASSPORT_STATUS_WITH_PASSPORT_VALUE')){
            $exp_date = new Carbon($employee['passport_expiration_date']);
        }
        elseif($employee->passport_status == config('constants.PASSPORT_STATUS_WITH_APPOINTMENT_VALUE')) {
            $exp_date = new Carbon($employee['date_of_appointment']);
        }
        elseif($employee->passport_status == config('constants.PASSPORT_STATUS_WAITING_FOR_DELIVERY_VALUE')) {
            $exp_date = new Carbon($employee['date_of_delivery']);
        }
        
        $duration = $exp_date->diff($today);
        
        // Determine if date is in the future from today
        $employee["is_date_passed"] = $exp_date->lt($today);

        // Determine if Passport alert is warning(red) or info(blue)
        $employee["passport_isWarning"] = true;

        // Determine if Passport alert is to be displayed
        $employee["passport_isAlertDisplayed"] = true;

        $employee["passport_duration_string"];

        $isYearNotZero = $duration->y != 0;
        $isMonthNotZero = $duration->m != 0;
        $isDayNotZero = $duration->d != 0;

        if($isYearNotZero) {
            $employee["passport_duration_string"] .= $duration->y . (($duration->y == 1) ? " year " : " years ");
        }

        if($isMonthNotZero) {
            $employee["passport_duration_string"] .= ($employee["passport_duration_string"] != "" ? " and " : "");
            $employee["passport_duration_string"] .= $duration->m . (($duration->m == 1) ? " month " : " months ");
        }

        if($isDayNotZero && 
            ((($isYearNotZero xor $isMonthNotZero)) || 
            (!($isYearNotZero && $isMonthNotZero) ))
        ){
            $employee["passport_duration_string"] .= ($employee["passport_duration_string"] != "" ? " and " : "");
            $employee["passport_duration_string"] .= ($duration->d) . (($duration->d == 1) ? " day " : " days ");
        }
        
        if($today->equalTo($exp_date)) {
            $employee["passport_duration_string"] = "today";
        }
        
        // Determine the time difference for duration        
        $duration->years = Carbon::now()->diffInYears($exp_date);
        $duration->months = Carbon::now()->diffInMonths($exp_date);
        $duration_days = Carbon::now()->diffInDays($exp_date);

        // IF (with Passport)
        if($employee->passport_status == config('constants.PASSPORT_STATUS_WITH_PASSPORT_VALUE'))
        {
            // When to display the notification
            $employee["passport_isAlertDisplayed"] = ($duration->months >= config('constants.PASSPORT_STATUS_1_INFO_START')["value"] && !$employee["is_date_passed"]) ? false : true;
            if(!$employee["is_date_passed"] && $duration->months >= config('constants.PASSPORT_STATUS_1_WARNING_START')["value"]) {
                $employee["passport_isWarning"] = false;
            } else {
                $employee["passport_isWarning"] = true;
            }
        }

        // IF (Appointment or Delivery)
        if(($employee->passport_status == config('constants.PASSPORT_STATUS_WITH_APPOINTMENT_VALUE') || 
            $employee->passport_status == config('constants.PASSPORT_STATUS_WAITING_FOR_DELIVERY_VALUE')))
        {
            // When to display the notification
            $employee["passport_isAlertDisplayed"] = ($duration->months >= config('constants.PASSPORT_STATUS_2_INFO_START')["value"] && !$employee["is_date_passed"]) ? false : true;
            if(!$employee["is_date_passed"] && $duration_days >= config('constants.PASSPORT_STATUS_2_WARNING_START')["value"]) { 
                $employee["passport_isWarning"] = false;
            } else {
                $employee["passport_isWarning"] = true;
            }
        }

        // IF (no passport/appointment/delivery)
        if($employee->passport_status == config('constants.PASSPORT_STATUS_WITHOUT_PASSPORT_VALUE'))
        {
            $employee["passport_isAlertDisplayed"] = true;
            $employee["passport_isWarning"] = true;
        }

        // If user is devj-portal do not display passport warning
        if ($employee['email'] == config('constants.SYSTEM_EMAIL')) {
            $employee["passport_isAlertDisplayed"] = false;
        }

        $employee["passport_duration_string"] = ($employee["passport_duration_string"] != "" ? $employee["passport_duration_string"] : "null");

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
                
        // ----------------------------- No Appointment ---------------------------- //
        if($employee['passport_status'] == config('constants.PASSPORT_STATUS_WITHOUT_PASSPORT_VALUE'))  {
            $passport_message = "Please consider setting a passport appointment as soon as possible.";
        }

        // ----------------------------- With passport ----------------------------- //
        // Date is today onwards
        elseif($employee['passport_status'] == config('constants.PASSPORT_STATUS_WITH_PASSPORT_VALUE') && !$employee['is_date_passed']) {
            if(str_contains($employee['passport_duration_string'], "today")) {
                $passport_message = "Passport expires today!";
            }
            else {
                $passport_message = "Passport expires in " . $employee['passport_duration_string'];
            }
        }
        // Date < today (Expired)
        elseif($employee['passport_status'] == config('constants.PASSPORT_STATUS_WITH_PASSPORT_VALUE') && $employee['is_date_passed']) {
            $passport_message = "Passport has expired!";
        }

        // ---------------------------- For Appointment ---------------------------- //
        // Date is today onwards
        elseif($employee['passport_status'] == config('constants.PASSPORT_STATUS_WITH_APPOINTMENT_VALUE') && !$employee['is_date_passed']){
            if(str_contains($employee['passport_duration_string'], "today")) {
                $passport_message = "Passport appointment is today!";
            }
            else {
                $passport_message = "Passport appointment is in " . $employee['passport_duration_string'];
            }
        }
        // Date < today
        elseif($employee['passport_status'] == config('constants.PASSPORT_STATUS_WITH_APPOINTMENT_VALUE') && $employee['is_date_passed']){
            $passport_message = "Passport appointment was " . $employee['passport_duration_string'] . " ago!";
        }

        // ------------------------------ For Delivery ----------------------------- //
        // Date is today onwards
        elseif($employee['passport_status'] == config('constants.PASSPORT_STATUS_WAITING_FOR_DELIVERY_VALUE') && !$employee['is_date_passed']){
            if(str_contains($employee['passport_duration_string'], "today")) {
                $passport_message = "Passport delivery is today!";
            }
            else {
                $passport_message = "Passport delivery is in  " . $employee['passport_duration_string'];
            }
        }
        // Date < today
        elseif($employee['passport_status'] == config('constants.PASSPORT_STATUS_WAITING_FOR_DELIVERY_VALUE') && $employee['is_date_passed']){
            $passport_message = "Passport delivery was " . $employee['passport_duration_string'] . " ago!";
        }
        

        return $passport_message;
    }

    
    
    /**
     * Get the full name of employee
     *
     * @param [type] $employee
     * @return string
     */
    static function getFullName($employee, $isWithMiddleName = false) {
        if(is_array($employee)) {
            if($isWithMiddleName) {
                return $employee['first_name'] .' ' . (!empty($employee['middle_name']) ? " " . $employee['middle_name'] : "") .' ' . $employee['last_name'] . (!empty($employee['name_suffix']) ? " " . $employee['name_suffix'] : "");
            } else {
                return $employee['first_name'] .' ' . $employee['last_name'] . (!empty($employee['name_suffix']) ? " " . $employee['name_suffix'] : "");
            }
        } else {
            if($isWithMiddleName) {
                return $employee->first_name .' ' . (!empty($employee->middle_name) ? " " . $employee->middle_name : "") .' ' . $employee->last_name . (!empty($employee->name_suffix) ? " " . $employee->name_suffix : "");
            } else {
                return $employee->first_name .' ' . $employee->last_name . (!empty($employee->name_suffix) ? " " . $employee->name_suffix : "");
            }
        }
    }
    
    /**
     * Get the full name of employee
     *
     * @param [type] $employee
     * @return string
     */
    static function getFullName_lastNameFirst($employee, $isWithMiddleName = false) {
        if(is_array($employee)) {
            if($isWithMiddleName) {
                return $employee['last_name'] . ', ' . $employee['first_name'] .' ' . (!empty($employee['middle_name']) ? " " . $employee['middle_name'] : "") . (!empty($employee['name_suffix']) ? " " . $employee['name_suffix'] : "");
            } else {
                return $employee['last_name'] . ', ' . $employee['first_name'] . (!empty($employee['name_suffix']) ? " " . $employee['name_suffix'] : "");
            }
        } else {
            if($isWithMiddleName) {
                return $employee->last_name . ', ' . $employee->first_name . (!empty($employee->middle_name) ? " " . $employee->middle_name : "") . (!empty($employee->name_suffix) ? " " . $employee->name_suffix : "");
            } else {
                return $employee->last_name . ', ' . $employee->first_name . (!empty($employee->name_suffix) ? " " . $employee->name_suffix : "");
            }
        }
    }

    
}
