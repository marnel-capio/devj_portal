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
                            ->where('active_status', 1)
                            ->get()
                            ->toArray();
        
    }

    static function getEmployeeNameList(){
        return self::selectRaw('id, CONCAT(last_name, ", ", first_name) AS employee_name')
                    ->where('active_status', 1)
                    ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                    ->orderBy('last_name', 'asc')
                    ->orderBy('first_name', 'asc')
                    ->get()
                    ->toArray();
    }
}
