<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeesProjects extends Model
{
    use HasFactory;

    protected $table = 'employees_projects';
    protected $guarded = [];
    
    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';

    static function getProjectsByEmployee($id){
        
        return self::selectRaw('employees_projects.start_date
                                ,employees_projects.end_date
                                ,projects.name
                                ,projects.id as project_id
                                ,case when isnull(projects.end_date) 
                                    then "Ongoing" 
                                    else "Ended"
                                    end as project_status'
                                )

                    ->leftJoin('projects', 'projects.id',  'employees_projects.project_id')
                    ->where('employees_projects.employee_id', $id)
                    ->where('employees_projects.approved_status', 2)
                    ->orderByRaw('case when isnull(employees_projects.end_date) then 0 else 1 end ASC
                                    , employees_projects.end_date DESC')
                    ->get()
                    ->toArray();
                    
    }

    static function checkIfProjectIsOngoing($projectId, $employeeId){
        $detail = self::where('project_id', $projectId)
                        ->where('employee_id', $employeeId)
                        ->where(function($query){
                            $query->whereNull('end_date')
                                ->orWhere('end_date', '0')
                                ->orWhere('end_date', '0000-00-00 00:00:00');
                            })
                            ->get()
                            ->toArray();
        return !empty($detail);
    }
}
