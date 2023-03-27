<?php

namespace App\Models;

use App\Mail\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Projects extends Model
{
    use HasFactory;

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';
    protected $guarded = [];

    static function getProjectDropdownPerEmployee($id){

        return self::select('id', 'name', 'start_date', 'end_date')
                ->whereNotIn('id', function($query) use ($id){
                                        $query->select('project_id')
                                                ->from('employees_projects')
                                                ->where('employee_id', $id)
                                                ->where(function($query) {
                                                    $query->where('approved_status', config('constants.APPROVED_STATUS_PENDING'))
                                                            ->orWhere(function($query) {
                                                                $query->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                                                ->whereRaw('(end_date IS NULL OR end_date > CURDATE())');
                                                            });
                                                });
                                    })
                ->get()
                ->toArray();
                
    }

    static function getProjectDropdownPersoftware($id){
        $project_list = self::select('id', 'name', 'start_date', 'end_date')
                ->whereNotIn('id', function($query) use ($id){
                                        $query->select('project_id')
                                                ->from('projects_softwares')
                                                ->where('software_id', $id);
                                                
                                    })
                ->get()
                ->toArray();

        return $project_list;
                
    }    
}
