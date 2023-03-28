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
                                softwares.id,
                                softwares.software_name,
                                software_types.type_name as type,
                                softwares.approved_status,
                                softwares.updated_by,
                                softwares.remarks
                            ')
                        ->leftJoin('software_types', 'software_types.id',  'softwares.software_type_id')                            
                        ->whereIn('softwares.approved_status', [config('constants.APPROVED_STATUS_PENDING'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);


        if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE')){
            //get all software request of the current user only
            $query->where('softwares.updated_by', Auth::user()->id);
        }
        $query->orderBy('softwares.software_name', 'ASC');
        return $query->get()->toArray();
    }

    static function getSoftwareForList($keyword = '', $status = '', $type = '')
    {
        $query = self::selectRaw('
                                softwares.id,
                                softwares.approved_by,
                                softwares.approved_status,
                                software_types.type_name as type,
                                softwares.software_name,
                                softwares.remarks,
                                softwares.reasons,
                                softwares.update_data,
                                softwares.created_by,
                                softwares.updated_by,
                                softwares.create_time,
                                softwares.update_time,
                                softwares.reject_code,
                                softwares.approve_time
                            ')
                        ->leftJoin('software_types', 'software_types.id',  'softwares.software_type_id');

        if (!empty($keyword)) {
            $query=  $query->where('softwares.software_name','LIKE','%'.$keyword.'%');
        }
        
        if(!empty($status))
        {
            if($status != config('constants.SOFTWARE_FILTER_STATUS_ALL'))//status choses is all
            {
                $query = $query->where('softwares.approved_status','LIKE','%'.$status.'%');
            }

        }
        if(!empty($type))
        {
            if($type != config('constants.SOFTWARE_FILTER_TYPE_ALL'))//status choses is all
            {

                $query = $query->where('softwares.software_type_id','LIKE','%'.$type.'%');
            }

        }                                                      

        $query->orderBy('softwares.software_name', 'ASC');
        return $query->get()->toArray();

    }

    static function getSoftwareForDownload()
    {
        $query = self::selectRaw('
                            softwares.id,
                            softwares.approved_by,
                            softwares.approved_status,
                            software_types.type_name as type,
                            softwares.software_name,
                            softwares.remarks,
                            softwares.reasons,
                            softwares.update_data,
                            softwares.created_by,
                            softwares.updated_by,
                            softwares.create_time,
                            softwares.update_time,
                            softwares.reject_code,
                            softwares.approve_time,
                            softwares.software_type_id
                        ')
                        ->leftJoin('software_types', 'software_types.id',  'softwares.software_type_id')                            
                        ->whereIn('softwares.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                        ->orderBy('software_type_id', 'ASC')
                        ->orderBy('software_name', 'ASC');
        
        return $query->get()->toArray();

    }

    static function getSoftwareDetail($current_id)
    {
        $query = self::selectRaw('
                        softwares.id,
                        softwares.approved_status,
                        software_types.type_name as type,
                        software_types.approved_status as type_approved_status,
                        softwares.software_name,
                        softwares.remarks,
                        softwares.reasons,
                        softwares.update_data,
                        softwares.create_time,
                        softwares.update_time,
                        softwares.reject_code,
                        softwares.approve_time,
                        softwares.software_type_id,
                        CONCAT(e1.last_name, ", ", e1.first_name) AS creator,
                        CONCAT(e2.last_name, ", ", e2.first_name) AS updater,
                        CASE WHEN softwares.approved_by THEN CONCAT(e3.last_name, ", ", e3.first_name) ELSE "" END AS approver
                    ')
                ->leftJoin('software_types', 'software_types.id',  'softwares.software_type_id')  
                ->leftJoin('employees as e1', 'e1.id', 'softwares.created_by')
                ->leftJoin('employees as e2', 'e2.id', 'softwares.updated_by')
                ->leftJoin('employees as e3', 'e3.id', 'softwares.approved_by')                
                ->where('softwares.id', $current_id)
                ->orderBy('softwares.software_type_id', 'ASC')
                ->first();


        return $query;
    }

    static function GetLastApproverDetail()
    {
        $query = self::selectRaw('
                        softwares.approve_time,
                        CONCAT(employees.last_name, ", ", employees.first_name) AS approver
                    ')
                ->leftJoin('employees', 'employees.id', 'softwares.approved_by')
                ->whereIn('softwares.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                ->orderBy('approve_time', 'DESC')
                ->first();
       
        return $query;
    }
}
