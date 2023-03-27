<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectsRequest;
use App\Mail\Software;
use App\Models\Employees;
use App\Models\EmployeesProjects;
use App\Models\Logs;
use App\Models\Projects;
use App\Models\ProjectSoftwares;
use App\Models\Softwares;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectsController extends Controller
{
    public function index () {
        dd('project list coming soon');
    }
    
    public function create () {

        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE'), 403);
        return view('projects.create', ['isRegist' => true]);
    }

    public function regist (ProjectsRequest $request) {
        $request->validated();

        //save data in DB
        $insertData = $request->except('_token');
        $insertData['created_by'] = Auth::user()->id;
        $insertData['updated_by'] = Auth::user()->id;
        $id = Projects::create($insertData)->id;

        //create logs
        Logs::createLog('Projects', 'Project registration of ' .$insertData['name'] .'.');

        //add success message to session
        session(['regist_update_alert' => 'Project was successfully registered!']);

        return redirect(route('projects.details', ['id' => $id]));
    }

    public function detail ($id) {

        //get project data
        $projectData = Projects::where('id', $id)->first();
        $projectMembers = EmployeesProjects::getProjectMembersById($id);
        
        //get data for employee dropdown in Link Employee modal
        $employeeDropdown = [];
        if (in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')])) {
            $employeeDropdown = Employees::selectRaw('
                        id,
                        CONCAT(last_name, ", ", first_name) AS employee_name
                    ')
                    ->whereNotIn('id', function($query) use ($id){
                        $query->select('employee_id')
                            ->from('employees_projects')
                            ->where('project_id', $id)
                            ->where('approved_status', config('constants.APPROVED_STATUS_PENDING'))
                            ->orWhere(function($query) {
                                $query->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                ->whereRaw('(end_date IS NULL OR end_date > CURDATE())');
                            });

                    })
                    ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                    ->where('active_status', 1)
                    ->orderBy('last_name', 'asc')
                    ->orderBy('first_name', 'asc')
                    ->get()
                    ->toArray();
        } else {
            //check if current user is already a member of the project
            $employeeProjectData = EmployeesProjects::where('employee_id', Auth::user()->id)
                                                        ->where('project_id', $id)
                                                        ->whereRaw('(end_date IS NULL or end_date > CURDATE())')
                                                        ->get()
                                                        ->toArray();
            if ( empty($employeeProjectData) ) {
                $employeeDropdown = [[
                    'id' => Auth::user()->id,
                    'employee_name' => Auth::user()->last_name .", " .Auth::user()->first_name,
                ]];
            }
        }

        //get employee linkage requests
        $employeeLinkageRequests = [];
        if (Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')) {
            $employeeLinkageRequests = EmployeesProjects::selectRaw('
                    ep.*,
                    CONCAT(DATE_FORMAT(ep.start_date, "%Y-%m-%d"), " - ", CASE WHEN ep.end_date IS NULL THEN "" ELSE DATE_FORMAT(ep.end_date, "%Y-%m-%d") END) AS membership_date,
                    CONCAT(e.first_name, " ", e.last_name) AS data_name,
                    CONCAT(e.last_name, ", ", e.first_name) AS table_name
                ')
                ->from('employees_projects as ep')
                ->leftJoin('employees as e', 'e.id', 'ep.employee_id')
                ->where('ep.project_id', $id)
                ->whereIn('ep.approved_status', [config('constants.APPROVED_STATUS_PENDING'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                ->orderBy('ep.update_time', 'asc')
                ->orderBy('e.last_name', 'asc')
                ->orderBy('e.first_name', 'asc')
                ->get()
                ->toArray();
        }

        //getLinkedSoftwares
        $linkedSoftwares = ProjectSoftwares::selectRaw('
                s.software_name,
                st.type_name as software_type,
                s.remarks,
                ps.id,
                ps.software_id
            ')
            ->from('projects_softwares AS ps')
            ->leftJoin('softwares AS s', 's.id', 'ps.software_id')
            ->leftJoin('software_types AS st', 'st.id', 's.software_type_id')
            ->where('ps.project_id', $id)
            ->orderBy('s.software_name', 'asc')
            ->orderBy('ps.id', 'asc')
            ->get()
            ->toArray();

        //get software dropdown
        $softwareDropdown = Softwares::select('id', 'software_name')
                                    ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                    ->whereNotIn('id', function ($query) use ($id) {
                                        $query->select('software_id')
                                                ->from('projects_softwares')
                                                ->where('project_id', $id);
                                    })
                                    ->orderBy('software_name', 'asc')
                                    ->get()
                                    ->toArray();


        return view('projects.details', [
            'projectData' => $projectData,
            'isManager' => Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE'),
            'detailNote' => '', 
            'showAddBtn' => true,   //fix later
            'projectMembers' => $projectMembers,
            'employeeDropdown' => $employeeDropdown,
            'employeeLinkageRequests' => $employeeLinkageRequests,
            'linkedSoftwares' => $linkedSoftwares,
            'softwareDropdown' => $softwareDropdown,


        ]);
    }

    public function edit ($id) {

        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE'), 403);

        //get project data
        $projectData = Projects::where('id', $id)->first();


        return view('projects.create', [
            'project' => $projectData,
            'isRegist' => false,
        ]);
    }

    public function store (ProjectsRequest $request) {
        $request->validated();

        //update data in DB
        $id = $request->input('id');
        $updateData = $request->except(['_token', 'id']);
        $updateData['updated_by'] = Auth::user()->id;
        Projects::where('id', $id)->update($updateData);

        //create logs
        Logs::createLog('Projects', 'Project detail update of ' .$updateData['name'] .'.');

        //add success message to session
        session(['regist_update_alert' => 'Project was successfully updated!']);

        return redirect(route('projects.details', ['id' => $id]));
    }

}
