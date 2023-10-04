<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EmployeesProjects extends Model
{
    use HasFactory;

    protected $table = 'employees_projects';
    protected $guarded = [];
    
    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';

    /**
     * Retrieve projects of an employee
     *
     * @param [type] $id
     * @return void
     */
    static function getProjectsByEmployee($id){
        
        return self::selectRaw('employees_projects.start_date
                                ,employees_projects.end_date
                                ,projects.name
                                ,projects.id as project_id
                                ,case when projects.end_date IS NULL 
                                    then "Ongoing" 
                                    else 
                                        case when projects.end_date > CURDATE()
                                        then "Ongoing"
                                        else "Ended"
                                        end
                                    end as project_status'
                                )

                    ->leftJoin('projects', 'projects.id',  'employees_projects.project_id')
                    ->where('employees_projects.employee_id', $id)
                    ->whereIn('employees_projects.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                    ->orderByRaw('
                                CASE WHEN employees_projects.end_date IS NULL 
                                THEN 0 
                                ELSE CASE WHEN employees_projects.end_date > CURDATE() 
                                    THEN 0
                                    ELSE 1 
                                    END 
                                END ASC,
                                CASE WHEN employees_projects.end_date IS NULL 
                                THEN 0 
                                ELSE 1
                                END ASC,
                                employees_projects.end_date DESC,
                                projects.name ASC
                    
                    ')
                    ->get()
                    ->toArray();
                    
    }

    /**
     * Check if an employee is a current member of a project
     *
     * @param [type] $projectId
     * @param [type] $employeeId
     * @return void
     */
    static function checkIfProjectIsOngoing($projectId, $employeeId){
        $detail = self::where('project_id', $projectId)
                        ->where('employee_id', $employeeId)
                        ->where('approved_status',"!=", config('constants.APPROVED_STATUS_REJECTED'))
                        ->where(function($query){
                            $query->whereNull('end_date')
                                ->orWhere('end_date', '0000-00-00 00:00:00');
                            })
                            ->get()
                            ->toArray();
        return !empty($detail);
    }

    static function getProjectMembersById ($id) {
        return self::selectRaw('
                    ep.*,
                    CONCAT(DATE_FORMAT(ep.start_date, "%Y/%m/%d"), " - ", CASE WHEN ep.end_date IS NULL THEN "\'\'" ELSE DATE_FORMAT(ep.end_date, "%Y/%m/%d") END) AS membership_date,
                    CONCAT(e.last_name, ", ", e.first_name) AS member_name,
                    CONCAT(e.first_name, " ", e.last_name) AS member_name_update,
                    CASE WHEN ep.end_date IS NULL THEN 1 ELSE CASE WHEN  DATE_FORMAT(ep.end_date, "%Y-%m-%d") > CURDATE() THEN 1 ELSE 0 END END AS isActive

                ')
                ->from('employees_projects AS ep')
                ->leftJoin('employees AS e', 'e.id', 'ep.employee_id')
                ->where('ep.project_id', $id)
                ->whereIn('ep.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                ->orderByRaw('CASE WHEN ep.end_date IS NULL THEN 0 ELSE CASE WHEN ep.end_date < CURDATE() THEN 1 ELSE 0 END END ASC')
                ->orderBy('ep.project_role_type', 'asc')
                ->orderBy('e.last_name', 'asc')
                ->orderBy('e.first_name', 'asc')
                ->get()
                ->toArray();
    }

    
    static function getActiveProjectMembersById ($id) {
        return self::selectRaw('
                    ep.*,
                    CONCAT(DATE_FORMAT(ep.start_date, "%Y/%m/%d"), " - ", CASE WHEN ep.end_date IS NULL THEN "\'\'" ELSE DATE_FORMAT(ep.end_date, "%Y/%m/%d") END) AS membership_date,
                    CONCAT(e.last_name, ", ", e.first_name) AS member_name,
                    CONCAT(e.first_name, " ", e.last_name) AS member_name_update,
                    CASE WHEN ep.end_date IS NULL THEN 1 ELSE CASE WHEN  DATE_FORMAT(ep.end_date, "%Y-%m-%d") > CURDATE() THEN 1 ELSE 0 END END AS isActive

                ')
                ->from('employees_projects AS ep')
                ->leftJoin('employees AS e', 'e.id', 'ep.employee_id')
                ->where('ep.project_id', $id)
                ->whereIn('ep.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                ->get();
    }

    
    static function employeeLinkageRequests ($project_id) {
        return self::selectRaw('
                    ep.*,
                    CONCAT(DATE_FORMAT(ep.start_date, "%Y-%m-%d"), " - ", CASE WHEN ep.end_date IS NULL THEN "\'\'" ELSE DATE_FORMAT(ep.end_date, "%Y-%m-%d") END) AS membership_date,
                    CONCAT(e.first_name, " ", e.last_name) AS data_name,
                    CONCAT(e.last_name, ", ", e.first_name) AS table_name
                ')
                ->from('employees_projects as ep')
                ->leftJoin('employees as e', 'e.id', 'ep.employee_id')
                ->where('ep.project_id', $project_id)
                ->whereIn('ep.approved_status', [config('constants.APPROVED_STATUS_PENDING'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                ->orderBy('ep.update_time', 'asc')
                ->orderBy('e.last_name', 'asc')
                ->orderBy('e.first_name', 'asc')
                ->get();
    }

    static function getProjectEmployeeLinkRequest(){

        $query = self::selectRaw('
                                employees_projects.id,
                                employees_projects.project_id,
                                projects.name as project_name,
                                employees_projects.employee_id,
                                CONCAT(employees.last_name, ", ", employees.first_name) AS linked_employee,
                                employees_projects.approved_status,
                                employees_projects.start_date,
                                employees_projects.end_date,
                                employees_projects.reasons
                            ')
                        ->leftJoin('projects', 'projects.id',  'employees_projects.project_id')                            
                        ->leftJoin('employees', 'employees.id',  'employees_projects.employee_id');


        if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE')){
            $currentUserId = Auth::user()->id;
            //get all laptop request and laptop linkage requests of the current user only
            $query->where(function($query1) use ($currentUserId){
                $query1->where(function($query2) use ($currentUserId){
                    $query2->whereIn('employees_projects.approved_status', [config('constants.APPROVED_STATUS_REJECTED'),config('constants.APPROVED_STATUS_APPROVED')])
                            ->where('employees_projects.prev_updated_by', $currentUserId);
                })
                ->orWhere(function($query2) use ($currentUserId){
                    $query2->whereIn('employees_projects.approved_status',[config('constants.APPROVED_STATUS_PENDING'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                            ->where('employees_projects.updated_by', $currentUserId);
                });
            });
        } else {
            $query->whereIn('employees_projects.approved_status', [config('constants.APPROVED_STATUS_PENDING'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);
        }
        $query->orderBy('project_name', 'ASC');
        return $query->get()->toArray();
    }

}
