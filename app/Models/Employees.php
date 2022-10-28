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
    
    // protected $fillable = [
    //     'first_name',
    //     'last_name',
    //     'middle_name',
    //     'birthdate',
    //     'gender',
    //     'cellphone_number',
    //     'other_contact_number',
    //     'position',
    //     'roles',
    //     'email',
    //     'current_address_street',
    //     'current_address_city',
    //     'current_address_province',
    //     'current_address_postalcode',
    //     'permanent_address_street',
    //     'permanent_address_city',
    //     'permanent_address_province',
    //     'permanent_address_postalcode',
    //     'server_manage_flag',
    //     'active_status',
    //     'reasons',
    // ];

    static function getEmailOfManagers(){
        return self::select('email')
                            ->where('roles', config('constants.MANAGER_ROLE_VALUE'))
                            ->where('active_status', 1)
                            ->get()
                            ->toArray();
        
    }
}
