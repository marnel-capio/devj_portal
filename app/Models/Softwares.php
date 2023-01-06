<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Softwares extends Model
{
    use HasFactory;

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';
    protected $guarded = [];

    static function getSoftwareRequest(){
        $query = self::selectRaw('
                                id,
                                approved_by,
                                approved_status,
                                type,
                                software_name,
                                remarks,
                                reasons,
                                update_data,
                                created_by,
                                updated_by,
                                create_time
                            ')
                        ->whereIn('approved_status', [config('constants.APPROVED_STATUS_PENDING'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);


        if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE')){
            //get all software request of the current user only
            $query->where('updated_by', Auth::user()->id);
        }

        return $query->get()->toArray();
    }
}
