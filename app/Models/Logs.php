<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Logs extends Model
{
    use HasFactory;

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';

    protected $guarded = [];

    static function createLog($module, $activity){
        $create = [
            'module' => $module,
            'activity' => $activity,
            'created_by' => Auth::user() != NULL ?  Auth::user()->id : 0,
            'updated_by' => Auth::user() != NULL ?  Auth::user()->id : 0,
        ];

        self::create($create); 
    }

    static function getLogOfUser($id)
    {
        return self::where('created_by', $id)
                ->orderBy('id', 'desc')
                ->get()->take("5")
                ->toArray();
    }
}
