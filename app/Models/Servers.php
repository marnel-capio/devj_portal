<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servers extends Model
{
    use HasFactory;

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';
    protected $guarded = [];

    static function getAllServer(){
        return self::selectRaw('id
                                ,server_name
                                ,hdd_status
                                ,ram_status
                                ,cpu_status
                                ,server_ip
                                ,function_role
                                ,CASE WHEN status THEN "Active" ELSE "Inactive" END AS status
                    ')
                    ->orderBy('server_name', 'asc')
                    ->get()
                    ->toArray();
    }
}
