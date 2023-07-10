<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectsRequest;
use App\Mail\Project as MailProjects;
use App\Models\Employees;
use App\Models\EmployeesProjects;
use App\Models\Logs;
use App\Models\Projects;
use App\Models\ProjectSoftwares;
use App\Models\Softwares;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

class ProjectsController extends Controller
{
    const PROJECT_REQUEST = 1;
    const PROJECT_LINK_REQUEST = 2;

    public function index () {
        $project_list = Projects::getProjectForList();

        return view('projects/list', ['project_list' => $project_list]);
    }
    
    public function create () {

        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE'), 403);
        return view('projects.create', ['isRegist' => true, 'project' => []]);
    }

    public function regist (ProjectsRequest $request) {
        $request->validated();

        //save data in DB
        $insertData = $request->except('_token');
        $insertData['created_by'] = Auth::user()->id;
        $insertData['updated_by'] = Auth::user()->id;
        $id = Projects::create($insertData)->id;

        //create logs
        Logs::createLog("Projects", 'Project registration of ' .$insertData['name'] .'.');

        //add success message to session
        session(['regist_update_alert' => 'Project was successfully registered!']);

        return redirect(route('projects.details', ['id' => $id]));
    }

    public function detailview($id)
    {

        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE'))
        {
           return($this->request($id));
        }

        return redirect(route('projects.details', ['id' => $id]));
    }

    public function detail ($id) {

        //get project data
        $projectData = Projects::where('id', $id)->first();

        abort_if(empty($projectData), 404);
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
                            ->where(function($query) {
                                $query->where('approved_status', config('constants.APPROVED_STATUS_PENDING'))
                                    ->orWhere(function($query) {
                                        $query->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                        ->whereRaw('(end_date IS NULL OR end_date > CURDATE())');
                                    });
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
        $linkedSoftwares = ProjectSoftwares::getLinkedSoftwareByProject($id);

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
        $originalData = Projects::getProjectOriginalData($id);
        Projects::where('id', $id)->update($updateData);
        
        //format log
        $log = "Project updated by manager: ";
        foreach($updateData as $key => $value){
            if($value != $originalData[$key] && !in_array($key, ['updated_by', 'password'])){
                $log .= "{$key}: {$originalData[$key]} > {$value}, ";
            }
        }
        $log = rtrim($log, ", ");

        Logs::createLog("Projects", $log);

        //add success message to session
        session(['regist_update_alert' => 'Project was successfully updated!']);

        return redirect(route('projects.details', ['id' => $id]));
    }

    public function removeLinkedSoftwareToProject (Request $request) {
        $linkageId = $request->input('id');
        $linkageData = ProjectSoftwares::where('id', $linkageId)->first();
        //validation
        if (empty($linkageData)) {
            //error
            session(['remove_soft_alert' => 'Invalid Request']);
        }

        //delete data in DB
        ProjectSoftwares::where('id', $linkageId)->delete();

        $projectData = Projects::where('id', $linkageData->project_id)->first();
        $softwareData = Softwares::where('id', $linkageData->software_id)->first();

        Logs::createLog("Projects", "Remove linkage of {$softwareData->software_name} to {$projectData->name}");

        session(['linked_soft_alert' => 'Software was successfully removed.']);

        return Redirect::back();
    }

    // Function for approving linkage requests: New Linkage or Linkage Update
    public function storeLinkage(Request $request){
        $id = $request->input('id');

        // 1. Validate IF ID is included in the request. ELSE, show error page
        $error = $this->validateRequest($id, self::PROJECT_LINK_REQUEST);
        if($error){
            return view('error.requestError')
                        ->with([
                            'error' => $error
                        ]);
        }

        $projectLinkDetails = EmployeesProjects::where('id', $id)->first();

        // 2. Get project data
        $projectData = Projects::where('id', $projectLinkDetails->project_id)->first();
        
        // 3. Get mail recipient
        $recipient = Employees::where('id', $projectLinkDetails->employee_id)->first();
        
        // 4. Get account to be linked
        if($projectLinkDetails->employee_id == $projectLinkDetails->updated_by){
            $requestor = $recipient;
        }else{
            $requestor = Employees::where('id', $projectLinkDetails->updated_by)->first();
        }

        // 5. Verify if status is Pending for approval
        if($projectLinkDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){

            // 5.1.1 Update the data to: Approved
            EmployeesProjects::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                        'updated_by' => Auth::user()->id,
                        'approved_by' => Auth::user()->id,
                    ]);

            // 5.1.2 Create log
            Logs::createLog("Projects", 'Project Linkage Request Approval');

            // 5.1.3 Mail the account to be linked
            $mailData = [
                'link' => route('projects.details', ['id' => $projectLinkDetails->project_id]),
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Project",
                'requestor' => !empty($requestor) ? $requestor->first_name .' ' .$requestor->last_name : 'unknown',
                'assignee' => $recipient->first_name .' ' .$recipient->last_name,
                'project_name' => $projectData->name,
            ];
            Mail::to($recipient->email)->send(new MailProjects($mailData, config('constants.MAIL_PROJECT_DETAIL_UPDATE_APPROVAL')));

            // 5.1.4 Alert message to be displayed to Request table in Project view
            $alert = 'Successfully approved the project linkage.';

        } else {
            // 5.2.1 Save temporary data
            $update = json_decode($projectLinkDetails->update_data, true);
            $update['updated_by'] = Auth::user()->id;
            $update['approved_by'] = Auth::user()->id;
            $update['update_data'] = NULL;
            $update['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            EmployeesProjects::where('id', $id)
                    ->update($update);

            // 5.2.2 Create logs
            Logs::createLog("Projects", 'Project Linkage Detail Update Approval');

            // 5.2.3 Send mail to requestor
            $mailData = [
                'link' => route('projects.details', ['id' => $projectLinkDetails->project_id]),
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Project",
                'requestor' => !empty($requestor) ? $requestor->first_name .' ' .$requestor->last_name : 'unknown',
                'assignee' => $recipient->first_name .' ' .$recipient->last_name,
                'project_name' => $projectData->name,
            ];

            Mail::to($recipient->email)->send(new MailProjects($mailData, config('constants.MAIL_PROJECT_NEW_LINKAGE_BY_NON_MANAGER_APPROVAL')));

            // 5.2.4 Alert message to be displayed to Request table in Project view
            $alert = 'Successfully approved the project linkage detail update.';
        }

        // 6. Save alert message to session
        session(['ela_alert'=> $alert]);
        return Redirect::back();
    }

    
    public function rejectLinkage(Request $request){
        $id = $request->input('id');

        // 1. Validate IF ID is included in the request. ELSE, show error page
        $error = $this->validateRequest($id, self::PROJECT_LINK_REQUEST);
        if($error){
            return view('error.requestError')
                        ->with([
                            'error' => $error
                        ]);
        }

        $projectLinkDetails = EmployeesProjects::where('id', $id)->first();
        $reason = $request->input('reason');

        // 2. Get project data
        $projectData = Projects::where('id', $projectLinkDetails->project_id)->first();

        // 3. Get mail recipient
        $recipient = Employees::where('id', $projectLinkDetails->employee_id)->first();

        // 4. Get requestor
        if($projectLinkDetails->employee_id == $projectLinkDetails->updated_by){
            $requestor = $recipient;
        }else{
            $requestor = Employees::where('id', $projectLinkDetails->updated_by)->first();
        }

        // 5. Verify if status is Pending for approval
        if($projectLinkDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){

            // 5.1.1 Update the data to: Reject
            EmployeesProjects::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                        'reasons' => $reason, 
                        'updated_by' => Auth::user()->id,
                        'approved_by' => Auth::user()->id,
                    ]);

            // 5.1.2 Create logs
            Logs::createLog("Projects", 'Project Linkage Request Rejection');

            // 5.1.3 Send mail to requestor
            $mailData = [
                'link' => route('projects.details', ['id' => $projectLinkDetails->project_id]),
                'reason' => $reason,
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Project",
                'requestor' => !empty($requestor) ? $requestor->first_name .' ' .$requestor->last_name : 'unknown',
                'assignee' => $recipient->first_name .' ' .$recipient->last_name,
                'project_name' => $projectData->name,
            ];

            Mail::to($recipient->email)->send(new MailProjects($mailData, config('constants.MAIL_PROJECT_DETAIL_UPDATE_REJECTION')));

            // 5.1.4 Alert message to be displayed to Request table in Project view
            $alert = 'Rejected project linkage.';
        }else{
            // 5.2.1 Reset the data
            $update['updated_by'] = Auth::user()->id;
            $update['approved_by'] = Auth::user()->id;
            $update['reasons'] = $reason;
            $update['update_data'] = NULL;
            $update['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            EmployeesProjects::where('id', $id)
                    ->update($update);

            // 5.2.2 Create logs
            Logs::createLog("Projects", 'Project Linkage Detail Update Rejection');

            // 5.3.3 Send mail to requestor
            $mailData = [
                'link' => route('projects.details', ['id' => $projectLinkDetails->project_id]),
                'reason' => $reason,
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Project",
                'requestor' => !empty($requestor) ? $requestor->first_name .' ' .$requestor->last_name : 'unknown',
                'assignee' => $recipient->first_name .' ' .$recipient->last_name,
                'project_name' => $projectData->name,
            ];

            Mail::to($recipient->email)->send(new MailProjects($mailData, config('constants.MAIL_PROJECT_NEW_LINKAGE_BY_NON_MANAGER_REJECTION')));
            
            // 5.2.4 Alert message to be displayed to Request table in Project view
            $alert = 'Rejected the detail update of project linkage.';    
        }

        // 6. Save alert message to session
        session(['elr_alert'=> $alert]);
        return Redirect::back();
    }
    
    /**
     * Additional validation for approval or rejection
     *
     * @param [type] $details
     * @return void
     */
    private function validateRequest($id, $type = self::PROJECT_REQUEST){

        
        // 1. Validate IF ID is included in the request. ELSE, show error page
        if(empty($id)){
            return 'Invalid request.';
        }

        $detail = $type == self::PROJECT_REQUEST ? Projects::where('id', $id)->first() : EmployeesProjects::where('id', $id)->first();

        if(empty($detail)){
            if($type == self::PROJECT_LINK_REQUEST){
                return 'Project linkage does not exist.';
            }else{
                return 'Project does not exist.';
            }
        }

        // Check if employee needs to be approved
        if($detail->approved_status != config('constants.APPROVED_STATUS_PENDING')    // Pending for new registration
            && $detail->approved_status != config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){    // Pending for update
            if($type == self::PROJECT_REQUEST){
                return 'Project has no pending request.';
            }else{
                return 'Project linkage has no pending request.';
            }
        }

        return ''; 
    }
}
