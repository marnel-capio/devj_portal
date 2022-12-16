<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
    
    static function getEmailOfManagers(){
        return self::select('email')
                            ->where('roles', config('constants.MANAGER_ROLE_VALUE'))
                            ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                            ->where('active_status', 1)
                            ->get()
                            ->toArray();
        
    }

    /**
     * Get all the names of active employees
     *
     * @return void
     */
    static function getEmployeeNameList(){
        return self::selectRaw('id, CONCAT(last_name, ", ", first_name) AS employee_name')
                    ->where('active_status', 1)
                    ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
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
                    ->whereIn('employees_laptops.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                    ->orderBy('employees.last_name', 'asc')
                    ->orderBy('employees.first_name', 'asc')
                    ->orderBy('laptops.tag_number', 'asc')
                    ->orderBy('employees_laptops.surrender_flag', 'asc')
                    ->orderBy('employees_laptops.surrender_date', 'asc')
                    ->orderBy('employees_laptops.created_by', 'desc')
                    ->orderBy('laptops.tag_number', 'asc')
                    ->get()
                    ->toArray();
    }
}
