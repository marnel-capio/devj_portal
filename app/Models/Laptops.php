<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laptops extends Model
{
    use HasFactory;

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';

    static function getLaptopDropdown(){

        return self::select('id', 'tag_number', )
                ->where('status', 1)
                ->whereNotIn('id', function($query){
                                        $query->select('laptop_id')
                                                ->from('employees_laptops')
                                                ->where('surrender_flag', 0)
                                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);
                                    })
                ->get()
                ->toArray();
                
    }

    static function getLaptopEmployeeDetails($id){
        return self::where('status', 1)
                    ->where('id', $id)
                    ->whereNotIn('id', function($query){
                        $query->select('laptop_id')
                                ->from('employees_laptops')
                                ->where('surrender_flag', 0)
                                ->where('approved_status', 2);
                    })
            ->get()
            ->toArray();
    }
}
