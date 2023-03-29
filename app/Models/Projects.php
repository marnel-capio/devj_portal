<?php

namespace App\Models;

use App\Mail\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

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


    static function getProjectForList($keyword = '', $status = '')
    {
        $query = self::selectRaw('
                                id,
                                name,
                                start_date,
                                end_date,
                                IF(end_date, "Finish", "On-Going") as status
                            ');

        if (!empty($keyword)) {
            $query=  $query->where('name','LIKE','%'.$keyword.'%');
        }
        

        if(!empty($status))
        {
            if($status == config('constants.PROJECT_STATUS_FILTER_ONGOING'))//status is on going
            {
                $query = $query->whereNull('end_date');
            }
            else if($status == config('constants.PROJECT_STATUS_FILTER_FINISH'))//status is finish
            {
                $query = $query->whereNotNull('end_date');

            }

        }
        $query->orderBy('name', 'ASC');
        return $query->get()->toArray();

    }

}
