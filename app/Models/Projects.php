<?php

namespace App\Models;

use Carbon\Carbon;
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
                                IF(end_date IS NOT NULL AND end_date<=CURRENT_DATE, "' . config("constants.PROJECT_STATUS_FINISH_TEXT") . '", "' . config("constants.PROJECT_STATUS_ONGOING_TEXT") . '") as status
                            ');

        if (!empty($keyword)) {
            $query=  $query->where('name','LIKE','%'.$keyword.'%');
        }
        

        // To get projects for: Filter in Project List
        if(!empty($status))
        {
            // Filter for: Status is On-Going
            if($status == config('constants.PROJECT_STATUS_FILTER_ONGOING')) 
            {
                $query = $query ->whereNull('end_date')
                                ->orWhere('end_date', ">", Carbon::today());
            }
            // Filter for: Status is Finished
            else if($status == config('constants.PROJECT_STATUS_FILTER_FINISH'))
            {
                $query = $query ->whereNotNull('end_date')
                                ->whereDate('end_date', "<=", Carbon::today());

            }

        }
        $query->orderBy('name', 'ASC');
        return $query->get()->toArray();

    }

}
