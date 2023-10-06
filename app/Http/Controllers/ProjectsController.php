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
        Logs::createLog("Project", 'Project registration of ' .$insertData['name'] .'.');

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

        foreach($projectMembers as $key => $employee) {
            $projectMembers[$key]['member_name_update'] = Employees::getFullName($employee);
            $projectMembers[$key]['member_name'] = Employees::getFullName_lastNameFirst($employee);
        }
        
        //get data for employee dropdown in Link Employee modal
        $employeeDropdown = [];
        if (in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')])) {
            $employeeDropdown = Employees::selectRaw('
                        id,
                        last_name,
                        first_name,
                        name_suffix
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

                    foreach($employeeDropdown as $key => $employee) {
                        $employeeDropdown[$key]['employee_name'] = Employees::getFullName_lastNameFirst($employee);
                    }
        } else {
            //check if current user is already a member of the project
            $employeeProjectData = EmployeesProjects::where('employee_id', Auth::user()->id)
                                                        ->where('project_id', $id)
                                                        ->where('approved_status', "!=",config('constants.APPROVED_STATUS_REJECTED'))
                                                        ->whereRaw('(end_date IS NULL or end_date > CURDATE())')
                                                        ->get()
                                                        ->toArray();
            if ( empty($employeeProjectData) ) {
                $employeeDropdown = [[
                    'id' => Auth::user()->id,
                    'employee_name' => Employees::getFullName_lastNameFirst(Auth::user()),
                ]];
            }
        }

        //get employee linkage requests
        $employeeLinkageRequests = [];
        if (Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')) {
            $employeeLinkageRequests = EmployeesProjects::employeeLinkageRequests($id)
                ->toArray();
        } else {
            // Get requests of logged in employee
            $employeeLinkageRequests = EmployeesProjects::employeeLinkageRequests($id)
                ->where('employee_id', Auth::user()->id)
                ->toArray();
        }

        

        foreach($employeeLinkageRequests as $key => $employee) {
            $employeeLinkageRequests[$key]['data_name'] = Employees::getFullName($employee);
            $employeeLinkageRequests[$key]['table_name'] = Employees::getFullName_lastNameFirst($employee);
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
                                    ->where('softwares.is_deleted',"!=",1)
                                    ->orderBy('software_name', 'asc')
                                    ->get()
                                    ->toArray();
        $showAddBtn = false;

        if( Auth::user()->roles == config('constants.ENGINEER_ROLE_VALUE')) { 
            if (count($employeeLinkageRequests) < 1 && 
                EmployeesProjects::getActiveProjectMembersById($id)->where('isActive', 1)->where('employee_id', Auth::user()->id)->count() == 0) {
                $showAddBtn = true;
            }
        } else {
            $showAddBtn = true;
        }

        // Check if member have no request to current project
        foreach ($projectMembers as $key => $member) {
            $projectMembers[$key]['haveNoRequest'] = true;

            foreach($employeeLinkageRequests as $member_req){
                if(($member['employee_id'] == $member_req['employee_id'])) {
                    $projectMembers[$key]['haveNoRequest'] = false;
                    break;
                }
            }
        }

        return view('projects.details', [
            'projectData' => $projectData,
            'isManager' => Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE'),
            'detailNote' => '', 
            'showAddBtn' => $showAddBtn,
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
        $log = "Project updated by manager. Update data: {";
        foreach($updateData as $key => $value){
            if($value != $originalData[$key] && !in_array($key, ['updated_by', 'password'])){
                $log .= "'$key': '{$originalData[$key]}' > '{$value}', ";
            }
        }
        $log = rtrim($log, ", ");
        $log .= "}";

        Logs::createLog("Project", $log);

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

        Logs::createLog("Project", "Remove linkage of {$softwareData->software_name} to {$projectData->name}");

        session(['linked_soft_alert' => 'Software was successfully removed.']);

        return Redirect::back();
    }

    /**
     * Store linkage detail.
     * 1. Creating linkage request: New Linkage or Linkage Update
     * 2. Saving updated data by manager
     *
     * @return bool
     */
    public function storeLinkage(Request $request){
        $id = $request->input('id');

        // Validate IF ID is included in the request. ELSE, show error page
        $error = $this->validateRequest($id, self::PROJECT_LINK_REQUEST);
        if($error){
            return view('error.requestError')
                        ->with([
                            'error' => $error
                        ]);
        }

        $projectLinkDetails = EmployeesProjects::where('id', $id)->first();
        $projectData = Projects::where('id', $projectLinkDetails->project_id)->first();        
        $recipient = Employees::where('id', $projectLinkDetails->employee_id)->first();
        if($projectLinkDetails->employee_id == $projectLinkDetails->updated_by){
            $requestor = $recipient;
        }else{
            $requestor = Employees::where('id', $projectLinkDetails->updated_by)->first();
        }

        // 5. If status is Pending for approval
        if($projectLinkDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){

            // 5.1.1 Update the data to: Approved
            EmployeesProjects::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                        'updated_by' => Auth::user()->id,
                        'approved_by' => Auth::user()->id,
                        'reasons' => null,
                        'prev_updated_by' => null,
                    ]);

            // 5.1.2 Create log
            Logs::createLog("Project", "Updated the linkage data of ". Employees::getFullName($recipient) ." to $projectData->name.");

            // 5.1.3 Mail the account to be linked
            $mailData = [
                'link' => route('projects.details', ['id' => $projectLinkDetails->project_id]),
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Project",
                'requestor' => !empty($requestor) ? Employees::getFullName($requestor) : 'unknown',
                'assignee' => Employees::getFullName($requestor),
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
            $update['prev_updated_by'] = NULL;
            $update['reasons'] = NULL;
            $update['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            EmployeesProjects::where('id', $id)
                    ->update($update);

            // 5.2.2 Create logs
            // Branches going here: Requested by admin/engineer approved by manager.
            Logs::createLog("Project", "Linked ". Employees::getFullName($recipient) ." to $projectData->name.");

            // 5.2.3 Send mail to requestor
            $mailData = [
                'link' => route('projects.details', ['id' => $projectLinkDetails->project_id]),
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Project",
                'requestor' => !empty($requestor) ? Employees::getFullName($requestor) : 'unknown',
                'assignee' => Employees::getFullName($requestor),
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



    // Function for 
    // 1. approving linkage requests: New Linkage or Linkage Update
    public function approveLinkage(Request $request){
        $id = $request->input('id');
        $alert = "";

        // 1. Validate IF ID is included in the request. ELSE, show error page
        $error = $this->validateRequest($id, self::PROJECT_LINK_REQUEST);
        if($error){
            return view('error.requestError')
                        ->with([
                            'error' => $error
                        ]);
        }

        $projectLinkDetails = EmployeesProjects::where('id', $id)->first();

        $projectData = Projects::where('id', $projectLinkDetails->project_id)->first();
        $recipient = Employees::where('id', $projectLinkDetails->employee_id)->first();
        if($projectLinkDetails->employee_id == $projectLinkDetails->updated_by){
            $requestor = $recipient;
        }else{
            $requestor = Employees::where('id', $projectLinkDetails->updated_by)->first();
        }

        // Approve request for: NEW EMPLOYEE-PROJECT LINKAGE
        if($projectLinkDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){

            EmployeesProjects::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                        'updated_by' => Auth::user()->id,
                        'approved_by' => Auth::user()->id,
                        'prev_updated_by' => null,
                        'reasons' => null,
                    ]);

            Logs::createLog("Project", "Approved the linkage of ". Employees::getFullName($recipient) ." to $projectData->name.");

            $mailData = [
                'link' => route('projects.details', ['id' => $projectLinkDetails->project_id]),
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Project",
                'requestor' => !empty($requestor) ? Employees::getFullName($requestor) : 'unknown',
                'assignee' => Employees::getFullName($requestor),
                'project_name' => $projectData->name,
            ];
            Mail::to($recipient->email)->send(new MailProjects($mailData, config('constants.MAIL_PROJECT_DETAIL_UPDATE_APPROVAL')));

            $alert = 'Successfully approved the project linkage.';

        } 

        // Approve request for: UPDATE DATA OF EMPLOYEE-PROJECT LINKAGE
        else {
            // Save temporary data
            $update = json_decode($projectLinkDetails->update_data, true);
            $update['updated_by'] = Auth::user()->id;
            $update['approved_by'] = Auth::user()->id;
            $update['update_data'] = NULL;
            $update['prev_updated_by'] = NULL;
            $update['reasons'] = NULL;
            $update['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            EmployeesProjects::where('id', $id)
                    ->update($update);

            // Create logs
            Logs::createLog("Project", "Approved the linkage update of ". Employees::getFullName($recipient) ." to $projectData->name.");

            // Send mail to requestor
            $mailData = [
                'link' => route('projects.details', ['id' => $projectLinkDetails->project_id]),
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Project",
                'requestor' => !empty($requestor) ? Employees::getFullName($requestor) : 'unknown',
                'assignee' => Employees::getFullName($requestor),
                'project_name' => $projectData->name,
            ];

            Mail::to($recipient->email)->send(new MailProjects($mailData, config('constants.MAIL_PROJECT_NEW_LINKAGE_BY_NON_MANAGER_APPROVAL')));

            // Alert message to be displayed to Request table in Project view
            $alert = 'Successfully approved the project linkage detail update.';
        }

        session(['ela_alert'=> $alert]);
        return Redirect::back();
    }

    
    public function rejectLinkage(Request $request){
        $id = $request->input('id');

        // Validate IF ID is included in the request. ELSE, show error page
        $error = $this->validateRequest($id, self::PROJECT_LINK_REQUEST);
        if($error){
            return view('error.requestError')
                        ->with([
                            'error' => $error
                        ]);
        }

        $projectLinkDetails = EmployeesProjects::where('id', $id)->first();
        $reason = $request->input('reason');

        $projectData = Projects::where('id', $projectLinkDetails->project_id)->first();
        $recipient = Employees::where('id', $projectLinkDetails->employee_id)->first();
        if($projectLinkDetails->employee_id == $projectLinkDetails->updated_by){
            $requestor = $recipient;
        }else{
            $requestor = Employees::where('id', $projectLinkDetails->updated_by)->first();
        }

        // Reject request for: NEW EMPLOYEE-PROJECT LINKAGE
        if($projectLinkDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){

            EmployeesProjects::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                        'reasons' => $reason, 
                        'updated_by' => Auth::user()->id,
                        'approved_by' => Auth::user()->id,
                        'prev_updated_by' => $projectLinkDetails->updated_by,
                    ]);

            Logs::createLog("Project", "Rejected the linkage of ". Employees::getFullName($recipient) ." to $projectData->name.");

            $mailData = [
                'link' => route('projects.details', ['id' => $projectLinkDetails->project_id]),
                'reason' => $reason,
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Project",
                'requestor' => !empty($requestor) ? Employees::getFullName($requestor) : 'unknown',
                'assignee' => Employees::getFullName($requestor),
                'project_name' => $projectData->name,
            ];

            Mail::to($recipient->email)->send(new MailProjects($mailData, config('constants.MAIL_PROJECT_DETAIL_UPDATE_REJECTION')));

            $alert = 'Rejected project linkage.';
        }

        // Reject request for: UPDATE DATA OF EMPLOYEE-PROJECT LINKAGE
        else {
            // Reset the data
            $update['updated_by'] = Auth::user()->id;
            $update['approved_by'] = Auth::user()->id;
            $update['reasons'] = $reason;
            $update['update_data'] = NULL;
            $update['prev_updated_by'] = $projectLinkDetails->updated_by;
            $update['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            EmployeesProjects::where('id', $id)
                    ->update($update);

            // Create logs
            Logs::createLog("Project", "Rejected the linkage update of ". Employees::getFullName($recipient) ." to $projectData->name.");

            // Send mail to requestor
            $mailData = [
                'link' => route('projects.details', ['id' => $projectLinkDetails->project_id]),
                'reason' => $reason,
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Project",
                'requestor' => !empty($requestor) ? Employees::getFullName($requestor) : 'unknown',
                'assignee' => Employees::getFullName($requestor),
                'project_name' => $projectData->name,
            ];

            Mail::to($recipient->email)->send(new MailProjects($mailData, config('constants.MAIL_PROJECT_NEW_LINKAGE_BY_NON_MANAGER_REJECTION')));
            
            // Alert message to be displayed to Request table in Project view
            $alert = 'Rejected the request to update project linkage.';    
        }

        // Save alert message to session
        session(['elr_alert'=> $alert]);
        return Redirect::back();
    }

    
    public function clearRejectedLinkage() {

        EmployeesProjects::where('prev_updated_by', Auth::user()->id)
                    ->update([
                        'updated_by' => Auth::user()->id,
                        'prev_updated_by' => null,
                    ]);
        //create logs
        Logs::createLog("Project", 'Rejected Project Linkage are all cleared.');
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
