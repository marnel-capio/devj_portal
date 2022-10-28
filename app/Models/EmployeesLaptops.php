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
}
