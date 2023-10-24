<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\BUTransfer;
use App\Http\Requests\ChangePassword;
use App\Http\Requests\LaptopsRequest;
use App\Http\Requests\LinkLaptop;
use App\Http\Requests\LinkProject;
use App\Http\Requests\LaptopLinkage;
use App\Mail\Employee;
use App\Mail\Software;
use App\Mail\Laptops as MailLaptops;
use App\Mail\Project;
use App\Models\Employees;
use App\Models\EmployeesLaptops;
use App\Models\EmployeesProjects;
use App\Models\ProjectSoftwares;
use App\Models\Laptops;
use App\Models\Logs;
use App\Models\Projects;
use App\Models\Servers;
use App\Models\ServersPartitions;
use App\Models\Softwares;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ApiController extends Controller
{   
    /**
     * Change Password API
     *
     * @param ChangePassword $request
     * @return void
     */
    public function changePassword(ChangePassword $request){
        
        $request->validated();
        $data = $request->only(['id', 'new_password']);

        //save new password
        Employees::where('id', $data['id'])
        ->update(['password' => password_hash($data['new_password'], PASSWORD_BCRYPT), 'updated_by' => Auth::user()->id]);

        Logs::createLog("Employee", "Updated password");

        return response()->json(['success' => true], 200);
    }

    /**
     * laptop linkage in employee detail screen
     *
     * @param LinkLaptop $request
     * @return void
     */
    public function linkLaptop(LinkLaptop $request){
        $request->validated();

        $data = $request->except(['_token', ]);

        $insertData = [
            'laptop_id' => $data['laptop_id'],
            'employee_id' => $data['employee_id'],
            'brought_home_flag' => $data['brought_home_flag'] ? 1 : 0,
            'vpn_flag' => $data['vpn_access_flag'] ? 1 : 0,
            'remarks' => $data['remarks'],
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ];

        $employee = Employees::where('id', $data['employee_id'])->first();
        $laptop = Laptops::where('id', $data['laptop_id'])->first();

        //check logined employee role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            //save directly in DB in db
            $insertData['approved_status'] =  config('constants.APPROVED_STATUS_APPROVED');
            $insertData['approved_by'] = Auth::user()->id;            
            EmployeesLaptops::create($insertData);

            if(Auth::user()->id != $data['employee_id']){
                $mailData = [
                    'link' => route('employees.details', ['id' => $employee->id]),
                    'first_name' => $employee->first_name,
                    'currentUserId' => Auth::user()->id,
                    'module' => "Employee",
                    'tagNumber' => $laptop->tag_number,
                ];
                $this->sendMailForEmployeeUpdate($employee->email, $mailData, config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_BY_MANAGER'));
            }
            $message = 'Added Successfully';
        }else{
            //if an employee edits his own data and is not the manager
            $insertData['approved_status'] = config('constants.APPROVED_STATUS_PENDING');

            EmployeesLaptops::create($insertData);

            //notify the managers of the request
            $mailData = [
                'link' => route('laptops.details', ['id' => $data['laptop_id']]) . '#link-req-tbl',
                'requestor' => Auth::user()->first_name .' ' .Auth::user()->last_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",
                'tagNumber' => $laptop->tag_number,
                'assignee' => $employee->first_name .' ' . $employee->last_name,
            ];

            $this->sendMailForEmployeeUpdate(Employees::getEmailOfManagers(), $mailData, config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_REQUEST'));
            $message = 'Your request has been sent';
        }
        session(['el_alert'=> $message]);
        Logs::createLog("Employee", "Link {$employee->first_name} {$employee->last_name} to {$laptop->tag_number} laptop");

        return response()->json(['success' => true, 
                                    'message' => $message, 
                                    'update' => EmployeesLaptops::getOwnedLaptopByEmployee($data['employee_id'])]
                                , 200);
    }
    


    /**
     * Link software to the project from Project details screen
     *
     * @param LinkProject $request
     * @return void
     */
    public function softwarelinkProject(LinkProject $request){
        $request->validated();


        //save data in db
        $data = $request->except(['_token', ]);

        $insertData = [
            'project_id' => $data['project_id'],
            'software_id' => $data['software_id'],
            'remarks' => $data['remarks'],
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ];

        $software = Softwares::where('id', $data['software_id'])->first();
        $project = Projects::where('id', $data['project_id'])->first();

        $message = 'Software added successfully';
        ProjectSoftwares::create($insertData);
        session(['sp_alert'=> $message]);
        //check logined employee role
        Logs::createLog("Software", "Link {$software->software_name} to {$project->name}");
        return response()->json(['success' => true, 
                                    'message' => $message, 
                                    'update' => ProjectSoftwares::getProjectBySoftware($data['software_id'])]
                                , 200);
    }

    /**
     * Used in linking a project in employee detail screen
     *
     * @param LinkProject $request
     * @return void
     */
    public function linkProjectToEmployee(LinkProject $request){
        $request->validated();

        //save data in db
        $data = $request->except(['_token', ]);

        $insertData = [
            'project_id' => $data['project_id'],
            'employee_id' => $data['employee_id'],
            'start_date' => $data['project_start'],
            'end_date' => $request->filled('project_end') ? $data['project_end'] : NULL,
            'project_role_type' => $data['project_role'],
            'onsite_flag' => $data['project_onsite'] ? 1 : 0,
            'remarks' => $data['remarks'],
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ];

        $employee = Employees::where('id', $data['employee_id'])->first();
        $project = Projects::where('id', $data['project_id'])->first();

        // For mail and logs
        $requestor = Auth::user()->first_name .' ' .Auth::user()->last_name;
        $employee->full_name = $employee->first_name .' ' .$employee->last_name;

        $message = '';
        $logMessage = "{$project->name} is linked to {$requestor}.";

        // Check logged-in employee's role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            // Save directly in DB
            $insertData['approved_status'] =  config('constants.APPROVED_STATUS_APPROVED');
            $insertData['approved_by'] = Auth::user()->id;        

            EmployeesProjects::create($insertData);

            if(Auth::user()->id != $data['employee_id']){
                $mailData = [
                    'link' => route('employees.details', ['id' => $employee->id]),
                    'first_name' => $employee->first_name,
                    'currentUserId' => Auth::user()->id,
                    'module' => "Employee",
                    'projectName' => $project->name,
                ];
                $this->sendMailForEmployeeUpdate($employee->email, $mailData, config('constants.MAIL_EMPLOYEE_PROJECT_LINK_BY_MANAGER'));
            }
            $message = 'Added ' . $project->name . ' Project Successfully';
            $logMessage = "Project '$project->name'  is linked to {$employee->full_name}";
        }else{
            // If an employee edits his own data and is not the manager

            $insertData['approved_status'] = config('constants.APPROVED_STATUS_PENDING');

            EmployeesProjects::create($insertData);

            // Notify managers of the request
            $mailData = [
                'link' => "/",  //update link
                'requestor' => $requestor,
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",
                'projectName' => $project->name,
                'assignee' => $employee->full_name,
            ];

            $this->sendMailForEmployeeUpdate(Employees::getEmailOfManagers(), $mailData, config('constants.MAIL_EMPLOYEE_PROJECT_LINK_REQUEST'));
            $message = 'Request for linkage has been sent';
            $logMessage = "{$requestor} requests to link {$project->name} project to {$employee->full_name}.";
            
            if(Auth::user()->id != $employee->id)
            {
                $logMessage = "{$requestor} requests to link {$project->name} project to {$employee->full_name}.";
            } else {
                $logMessage = "{$requestor} requests to link {$project->name} project.";
            }
        }
        
        Logs::createLog("Employee", $logMessage);
        session(['ep_alert'=> $message]);
        return response()->json(['success' => true, 
                                    'message' => $message, 
                                    'update' => EmployeesProjects::getProjectsByEmployee($data['employee_id'])]
                                , 200);
    }

    /**
     * send email
     * @param array $mailData
     * @param int $mailType
     * @return void
     */
    private function sendMailForEmployeeUpdate($recipients, $mailData, $mailType){
        Mail::to($recipients)->send(new Employee($mailData, $mailType));
    }

        /**
     * sendMailForSoftwareUpdate
     * @param array $mailData
     * @param int $mailType
     * @return void
     */
    private function sendMailForSoftwareUpdate($recipients, $mailData, $mailType){
        if (!empty($recipients)) {
            Mail::to($recipients)->send(new Software($mailData, $mailType));
        } 
    }

    /**
     * Employee search API
     *
     * @param Request $request
     * @return void
     */
    public function getEmployeeByFilter(Request $request){
        $searchFilter = [
            'keyword' => $request->get('keyword'),
            'filter' => $request->get('filter'),
            'status' => $request->get('status'),
            'passport' => $request->get('passport'),
        ];
        $employee = Employees::whereIn('approved_status', [2,4])->whereNot('email', config('constants.SYSTEM_EMAIL'));
                    
       // get employees
        if (!empty($searchFilter['keyword'])) {
            if ($searchFilter['filter'] == 1) {
                $employee = $employee->where(function($query) use ($searchFilter) {
                        $query->where('first_name','LIKE','%'.$searchFilter['keyword'].'%')
                                ->orWhere('last_name','LIKE','%'.$searchFilter['keyword'].'%')
                                ->orWhere('middle_name','LIKE','%'.$searchFilter['keyword'].'%');
                    });
            } else if ($searchFilter['filter'] == 2) {
                $employee = $employee->where('current_address_city','LIKE','%'.$searchFilter['keyword'].'%');
            } else if ($searchFilter['filter'] == 3) {
                $employee = $employee->where('current_address_province','LIKE','%'.$searchFilter['keyword'].'%');
            }
        }

        if (!empty($searchFilter['passport'])){
            if ($searchFilter['passport'] == 2) {
                $employee = $employee->where('passport_status',config('constants.PASSPORT_STATUS_WITH_PASSPORT_VALUE'));
            } else if ($searchFilter['passport'] == 3) {
                $employee = $employee->where('passport_status',config('constants.PASSPORT_STATUS_WAITING_FOR_DELIVERY_VALUE'));
            }else if ($searchFilter['passport'] == 4) {
                $employee = $employee->where('passport_status',config('constants.PASSPORT_STATUS_WITH_APPOINTMENT_VALUE'));
            } else if ($searchFilter['passport'] == 5) {
                $employee = $employee->where('passport_status',config('constants.PASSPORT_STATUS_WITHOUT_PASSPORT_VALUE'));
            }
        }

        if (Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            if ($searchFilter['status'] != 1) {
                if ($searchFilter['status'] == 4) {
                    $employee = $employee->where('bu_transfer_flag', 1);
                }else{
                    $status = $searchFilter['status'] == 2 ? 1 : 0;
                    $employee = $employee->where('active_status', $status);
                }
            }
        }else{
            $employee = $employee->where('active_status', 1);
        }

        $employee = $employee->orderBy('last_name', 'ASC')
                ->get();


        return json_encode($employee);
    }

    /**
     * Software search API
     *
     * @param Request $request
     * @return void
     */
    public function getSoftwareByFilter(Request $request){
        $searchFilter = [
            'keyword' => $request->get('keyword'),
            'status' => $request->get('status'),
            'type' => $request->get('type'),
        ];

        
        $softwarelist = Softwares::getSoftwareForList($searchFilter['keyword'], $searchFilter['status'], $searchFilter['type']);


        return response()->json([
            'success' => true,
            'update' => $softwarelist
        ]);
    }

    public function getProjectByFilter(Request $request){
        $searchFilter = [
            'keyword' => $request->get('keyword'),
            'status' => $request->get('status'),
        ];

        

        $projectlist = Projects::getProjectForList($searchFilter['keyword'], $searchFilter['status']);

        return response()->json([
            'success' => true,
            'update' => $projectlist
        ]);
    }



    public function filterLaptopList(Request $request){
        $data = $request->all();

        $laptopList = Laptops::getLaptopList($data['keyword'], $data['availability'], $data['status'], $data['searchFilter']);

        return response()->json([
                                'success' => true,
                                'update' => $laptopList
        ]);
    }

    /**
     * Laptop Detail Update Process
     *
     * @param LaptopsRequest $request
     * @return void
     */
    public function updateLaptopDetails(LaptopsRequest $request){
        $request->validated();

        $updateData = $request->except(['_token', 'isUpdate', 'edit_id']);
        if(!isset($updateData)){
            $updateData['status'] = 0;
        }
        $id = $request->input('edit_id');
        $originalData = Laptops::where('id', $id)->first();
        $dbReadyData = [];
        foreach($updateData as $key => $val){
            if($originalData[$key] != $val){
                $dbReadyData[$key] = $val;
            }
        }
        $updatedData = "";
        foreach($dbReadyData as $key => $val){
            if ($originalData[$key] != $val) {
                if ($key == "status") {
                    $val = $val == 0? "inactive" : "active";
                    $originalData[$key] = $originalData[$key] == 0? "inactive" : "active";
                }
                $field = str_replace("_", " ", $key);
                $updatedData .= "{$field}: {$originalData[$key]} -> {$val},";
            }
        }
        $updatedData = rtrim($updatedData,",");
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            //format log
            $log = 'Laptop Update: '.str_replace(",", ", ", $updatedData);
            

            //update data in DB
            $dbReadyData['updated_by'] = Auth::user()->id;
            $dbReadyData['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');
            $dbReadyData['approved_by'] = Auth::user()->id;
            Laptops::where('id', $id)
                    ->update($dbReadyData);

            Logs::createLog('Laptop', $log);

            session(['l_alert'=> 'Laptop detail was updated successfully.']);
        }else{
            Laptops::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'),
                        'update_data' => json_encode($dbReadyData, true),
                        'updated_by' => Auth::user()->id
                    ]);
            Logs::createLog('Laptop', 'Laptop Update: ' .str_replace(",", ", ", $updatedData));

            //send mail
            $recipients = Employees::getEmailOfManagers();

            $mailData = [
                'link' => "/laptops/{$id}/request",
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
                'updatedDetails' => explode(",", $updatedData),
            ];

            $this->sendMailForLaptop($recipients, $mailData, config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REQUEST'));

            session(['l_alert'=> 'Request for Laptop Detail Update has been sent']);
        }
        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Laptop  Linkage Detail Update Process
     *
     * @param LaptopLinkage $request
     * @return void
     */
    public function updateLaptopLinkage(LaptopLinkage $request){
        $request->validated();
        
        $updateData = $request->except(['_token', 'id']);
        $id = $request->input('id');

        $originalData = EmployeesLaptops::where('id', $id)->first();
        $dbReadyData = [];
        foreach($updateData as $key => $val){
            if($originalData[$key] != $val){
                $dbReadyData[$key] = $val;
            }
        }

        $laptopData = Laptops::where('id', $originalData->laptop_id)->first();
        $employeeData = Employees::where('id', $originalData->employee_id)->first();
        $updatedData = "";
        foreach($dbReadyData as $key => $val){
            if($originalData[$key] != $val){
                if (in_array($key, ["brought_home_flag","vpn_flag","surrender_flag"])) {
                    $val = $val == 0? "unset" : "set";
                    $originalData[$key] = $originalData[$key] == 0? "unset" : "set";
                }
                $field = str_replace("_", " ", $key);
                $updatedData .= "{$field}: {$originalData[$key]} -> {$val},";
            }
        }
        $updatedData = rtrim($updatedData,",");
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){

            //format log
            $log = 'Laptop Linkage Update: ' . str_replace(",", ", ", $updatedData);

            //update data in DB
            $dbReadyData['updated_by'] = Auth::user()->id;
            $dbReadyData['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');
            $dbReadyData['approved_by'] = Auth::user()->id;
            EmployeesLaptops::where('id', $id)
                    ->update($dbReadyData);

            Logs::createLog('Laptop', $log);

            $recipient = Employees::where('id', $originalData['employee_id'])->first();

            if($recipient->id != Auth::user()->id){
                //send mail
                $mailData = [
                    'link' => "/laptops/{$originalData['laptop_id']}",
                    'firstName' => $recipient['first_name'],
                    'currentUserId' => Auth::user()->id,
                    'module' => "Laptop",
                    'tagNumber' => $laptopData->tag_number,
                    'updatedDetails' => explode(",", $updatedData),
                ];
    
                $this->sendMailForLaptop($recipient['email'], $mailData, config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_MANAGER_NOTIF'));
            }

            session(['ul_alert'=> 'Laptop linkage was updated successfully.']);
        }else{
            EmployeesLaptops::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'),
                        'update_data' => json_encode($dbReadyData, true),
                        'updated_by' => Auth::user()->id
                    ]);
            Logs::createLog('Laptop', 'Latop Linkage Update: ' . str_replace(",", ", ", $updatedData));

            //send mail
            $recipients = Employees::getEmailOfManagers();

            $mailData = [
                'link' => "/laptops/{$originalData['laptop_id']}",
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
                'tagNumber' => $laptopData->tag_number,
                'requestor' => Auth::user()->first_name .' ' .Auth::user()->last_name,
                'assignee' => $employeeData->first_name .' ' .$employeeData->last_name,
                'updatedDetails' => explode(",", $updatedData),
            ];

            $this->sendMailForLaptop($recipients, $mailData, config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_REQUEST'));

            session(['ul_alert'=> 'Request for Laptop Linkage Update has been sent']);
        }
        

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Registration of new laptop linkage in laptop detail screen
     *
     * @param LaptopLinkage $request
     * @return void
     */
    public function registLaptopLinkage(LaptopLinkage $request){
        $request->validated();
        
        $requestData = $request->except(['_token']);
        $laptopData = Laptops::where('id', $requestData['id'])->first();
        $employeeData = Employees::where('id', $requestData['assignee'])->first();

        $insertData = [
            'laptop_id' => $requestData['id'],
            'employee_id' => $requestData['assignee'],
            'brought_home_flag' => $requestData['brought_home_flag'],
            'remarks' => $requestData['remarks'],
            'vpn_flag' => $requestData['vpn_flag'],
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ];

        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){

            //add data
            $insertData['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');
            $insertData['approved_by'] = Auth::user()->id;
            EmployeesLaptops::create($insertData);

            Logs::createLog('Laptop', "{$laptopData['tag_number']} laptop is linked to {$employeeData['last_name']}, {$employeeData['first_name']}");

            if($employeeData->id != Auth::user()->id){
                //send mail
                $mailData = [
                    'link' => "/laptops/{$requestData['id']}",
                    'firstName' => $employeeData['first_name'],
                    'currentUserId' => Auth::user()->id,
                    'module' => "Laptop",
                    'tagNumber' => $laptopData->tag_number,
                ];
    
                $this->sendMailForLaptop($employeeData['email'], $mailData, config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_MANAGER_NOTIF'));
            }

            session(['ll_alert'=> 'Laptop was linked successfully.']);

            $this->rejectOtherLinkageRequest($requestData['id']);
        }else{
            //add data
            $insertData['approved_status'] = config('constants.APPROVED_STATUS_PENDING');
            EmployeesLaptops::create($insertData);

            Logs::createLog('Laptop', "{$employeeData['last_name']}, {$employeeData['first_name']} requests to use {$laptopData['tag_number']} laptop");

            //send mail
            $recipients = Employees::getEmailOfManagers();

            $mailData = [
                'link' => "/laptops/{$requestData['id']}",
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
                'tagNumber' => $laptopData->tag_number,
                'requestor' => Auth::user()->first_name .' ' .Auth::user()->last_name,
                'assignee' => $employeeData->first_name .' ' .$employeeData->last_name,
            ];

            $this->sendMailForLaptop($recipients, $mailData, config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REQUEST'));

            session(['ul_alert'=> 'Request for Laptop Linkage has been sent']);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * send laptop related email
     * @param array $mailData
     * @param int $mailType
     * @return void
     */
    private function sendMailForLaptop($recipients, $mailData, $mailType){
        Mail::to($recipients)->send(new MailLaptops($mailData, $mailType));
    }

    private function rejectOtherLinkageRequest($laptop_id){
        $pendingApproval = EmployeesLaptops::getLinkRequestByLaptop($laptop_id, config('constants.APPROVED_STATUS_PENDING'));
        if(!empty($pendingApproval)){
            $reason = 'Laptop has been assigned to other employee';
            $pendingIds = array_column($pendingApproval, 'id');
            EmployeesLaptops::whereIn('id', $pendingIds)
                                ->update([
                                    'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                                    'approved_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id,
                                    'reasons' => $reason,
                                ]);
    
            Logs::createLog('Laptop', 'Laptop Linkage Request Rejection');
    
            //send mail
            foreach($pendingApproval as $request => $data){
                $mailData = [
                    'link' => route('laptops.details', ['id' => $laptop_id]),
                    'reason' => $reason,
                    'firstName' => $data['first_name'],
                    'currentUserId' => Auth::user()->id,
                    'module' => "Laptop",
                ];
                Mail::to($data['email'])->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REJECTION')));
            }
        }
    }

    /**
     * Deactivate employee
     *
     * @param Request $request
     * @return void
     */
    public function deactivateEmployee(Request $request){
        $employeeId = $request->input('id');
        $message = '';
        $success = false;

        if(empty($employeeId)){
            $message = 'Invalid Request!';
        }else{
            $employee = Employees::where('id', $employeeId)
                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                ->first();
            if(empty($employee)){
                $message = 'Invalid Request!';
            }elseif($employee->active_status == 0){
                $message = 'Employee has already been deactivated.';
            }else{
                //check if employee has no linked laptops
                $laptops = EmployeesLaptops::getOwnedLaptopByEmployee($employeeId);
                if(!empty($laptops)){
                    $notify = true;
                    $message = 'Deactivation is not allowed because laptop/s are still assigned to the employee.';

                    $mailData = [
                        'first_name' => $employee->first_name,
                        'laptops' => $laptops,
                        'module' => 'Employee',
                        'currentUserId' => Auth::user()->id,
                    ];

                }else{
                    $success = true;
                    //deactivate employee
                    Employees::where('id', $employeeId)
                                ->update([
                                    'active_status' => 0,
                                    'bu_transfer_flag' => 0,
                                    'bu_transfer_assignment' => NULL,
                                    'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                                    'updated_by' => Auth::user()->id,
                                    'approved_by' => Auth::user()->id,
                                ]);
                    
                    Logs::createLog('Employee', "Deactivated {$employee->first_name} {$employee->last_name}'s account");
                    
                    $mailData = [
                        'first_name' => $employee->first_name,
                        'module' => 'Employee',
                        'currentUserId' => Auth::user()->id,
                    ];

                    $this->sendMailForEmployeeUpdate($employee->email, $mailData, config('constants.MAIL_EMPLOYEE_DEACTIVATION'));
                    session(['success' => $success, 'message'=> 'Employee was successfully deactivated.']);
                }
            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Employee reactivation API
     *
     * @param Request $request
     * @return void
     */
    public function reactivateEmployee(Request $request){
        $employeeId = $request->input('id');
        $message = '';
        $success = false;

        if(empty($employeeId)){
            $message = 'Invalid Request!';
        }else{
            $employee = Employees::where('id', $employeeId)
                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                ->first();
            if(empty($employee)){
                $message = 'Invalid Request!';
            }elseif($employee->active_status == 1){
                $message = 'Employee is still active';
            }else{
                $success = true;
                //reactivate employee

                Employees::where('id', $employeeId)
                            ->update([
                                'active_status' => 1,
                                'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                                'updated_by' => Auth::user()->id,
                                'approved_by' => Auth::user()->id,
                            ]);
                
                Logs::createLog('Employee', "Reactivated {$employee->first_name} {$employee->last_name}'s account");
                

                $mailData = [
                    'first_name' => $employee->first_name,
                    'module' => 'Employee',
                    'currentUserId' => Auth::user()->id,
                ];

                $this->sendMailForEmployeeUpdate($employee->email, $mailData, config('constants.MAIL_EMPLOYEE_REACTIVATION'));
                session(['success' => $success, 'message'=> 'Employee was successfully reactivated.']);

            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * BU Transfer API (Employee is assigned to a different BU)
     *
     * @param Request $request
     * @return void
     */
    public function transferEmployee (BUTransfer $request) {
        $request->validated();
        $employeeId = $request->input('id');
        $selectedBUString = config('constants.BU_LIST.' .$request->input('bu_transfer_assignment'));
        $message = '';
        $success = false;

        if(empty($employeeId)){
            $message = 'Invalid Request!';
        }else{
            $employee = Employees::where('id', $employeeId)
                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                ->first();
            if(empty($employee)){
                $message = 'Invalid Request!';
            }elseif($employee->bu_transfer_flag == 1){
                $message = 'Employee has already been assigned to a different BU.';
            }else{
                $success = true;
                //transfer employee

                Employees::where('id', $employeeId)
                            ->update([
                                'bu_transfer_flag' => 1,
                                'bu_transfer_assignment' => $request->input('bu_transfer_assignment'),
                                'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                                'updated_by' => Auth::user()->id,
                                'approved_by' => Auth::user()->id,
                            ]);
                
                Logs::createLog('Employee', "Assigned {$employee->first_name} {$employee->last_name} to a different BU ({$selectedBUString})");

                session(['success' => $success, 'message'=> 'Account was successfully updated.']);
            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Employee reinstate api (employee is reinstated back to Dev J)
     *
     * @param Request $request
     * @return void
     */
    public function reinstateEmployee (Request $request) {
        $employeeId = $request->input('id');
        $message = '';
        $success = false;

        if(empty($employeeId)){
            $message = 'Invalid Request!';
        }else{
            $employee = Employees::where('id', $employeeId)
                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                ->first();
            if(empty($employee)){
                $message = 'Invalid Request!';
            }elseif($employee->bu_transfer_flag == 0){
                $message = 'Employee is already assigned to Dev J.';
            }else{
                $success = true;
                //reinstate employee

                Employees::where('id', $employeeId)
                            ->update([
                                'bu_transfer_flag' => 0,
                                'bu_transfer_assignment' => NULL,
                                'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                                'updated_by' => Auth::user()->id,
                                'approved_by' => Auth::user()->id,
                            ]);
                
                Logs::createLog('Employee', "Reassigned {$employee->first_name} {$employee->last_name} to Dev J");

                session(['success' => $success, 'message'=> 'Account was successfully updated.']);
            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Link employee to a project in project detail screen
     *
     * @param LinkProject $request
     * @return void
     */
    public function linkEmployeeToProject (LinkProject $request) {
        $request->validated();

        //save data in db
        $data = $request->except(['_token', ]);

        $insertData = [
            'project_id' => $data['project_id'],
            'employee_id' => $data['employee_id'],
            'start_date' => $data['project_start'],
            'end_date' => $request->filled('project_end') ? $data['project_end'] : NULL,
            'project_role_type' => $data['project_role'],
            'onsite_flag' => $data['project_onsite'] ? 1 : 0,
            'remarks' => $data['remarks'],
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ];

        $employee = Employees::where('id', $data['employee_id'])->first();
        $project = Projects::where('id', $data['project_id'])->first();

        // For mail and logs
        $requestor = Auth::user()->first_name .' ' .Auth::user()->last_name;
        $employee->full_name = $employee->first_name .' ' .$employee->last_name;

        $message = '';       
        // Check logged-in employee role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            // Save directly in DB
            $insertData['approved_status'] =  config('constants.APPROVED_STATUS_APPROVED');
            $insertData['approved_by'] = Auth::user()->id;        

            EmployeesProjects::create($insertData);

            if(Auth::user()->id != $data['employee_id']){
                $mailData = [
                    'link' => route('projects.details', ['id' => $project->id]) .'#requests',
                    'firstName' => $employee->first_name,
                    'project_name' => $project->name,
                    'currentUserId' => Auth::user()->id,
                    'module' => "Project",
                ];

                Mail::to($employee->email)->send(new Project($mailData, config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_BY_MANAGER')));
            }

            $message = 'Employee has been successfully linked.';
            $logMessage = "Linked {$employee->full_name} to {$project->name}.";
        }else{
            // if an employee edits his own data and is not a manager
            $insertData['approved_status'] = config('constants.APPROVED_STATUS_PENDING');

            EmployeesProjects::create($insertData);
            
            // notify managers of the request
            $mailData = [
                'link' => route('projects.details', ['id' => $project->id]) .'#requests',
                'requestor' => $requestor,
                'project_name' => $project->name,
                'member' => $employee->first_name .' ' .$employee->last_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Project",
            ];

            Mail::to(Employees::getEmailOfManagers())->send(new Project($mailData, config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_REQUEST')));

            $message = 'Request for linkage has been sent';
            // if(Auth::user()->id != $employee->id)
            // {
            //     $logMessage = "{$requestor} requests to link {$project->name} project to {$employee->full_name}.";
            // } else {
            //     $logMessage = "{$requestor} requests to link {$project->name} project.";
            // }
            $logMessage = "Linked {$employee->full_name} to project: {$project->name}.";
        }
        
        Logs::createLog("Project", $logMessage);

        session(['pj_alert'=> $message]);

        return response()->json(['success' => true, 
                                    'message' => $message, 
                                    'update' => EmployeesProjects::getProjectMembersById($data['project_id'])]
                                , 200);
    }



    /**
     * Update employee's project linkage data
     * Referrer: project details page -> Project Members table
     *
     * @param LinkProject $request
     * @return response()->json()
     */
    public function updateEmployeeProjectLinkage (LinkProject $request) {
        $request->validated();

        //save data in db
        $data = $request->except(['_token', 'linkage_id']);
        $linkageId = $request->input('linkage_id');
        $originalData = EmployeesProjects::where('id', $linkageId)->first();

        $updateData = [
            'start_date' => $data['project_start'],
            'end_date' => $request->filled('project_end') ? $data['project_end'] : NULL,
            'project_role_type' => $data['project_role'],
            'onsite_flag' => $data['project_onsite'] ? 1 : 0,
            'remarks' => $data['remarks'],
        ];

        $employee = Employees::where('id', $originalData['employee_id'])->first();
        $project = Projects::where('id', $originalData['project_id'])->first();

        $message = '';     
        $updatedData = "";
        foreach ($updateData as $key => $val) {
            if ($originalData->$key !== $val) {
                $tojson[$key] = $val;
                if (in_array($key, ["onsite_flag"])) {
                    $val = $val == 0 ? "unset" : "set";
                    $originalData[$key] = $originalData[$key] == 0? "unset" : "set";
                } else if ($key == 'project_role_type') {
                    $val = config('constants.PROJECT_ROLES')[$val];
                    $originalData[$key] = config('constants.PROJECT_ROLES')[$originalData[$key]];
                }
                $field = str_replace("_", " ", $key);
                $updatedData .= "{$field}: {$originalData[$key]} -> {$val},";
            }
        }  
        $updatedData = rtrim($updatedData,",");
        //check logined employee role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            //save directly in DB in db
            $updateData['approved_status'] =  config('constants.APPROVED_STATUS_APPROVED');
            $updateData['approved_by'] = Auth::user()->id;
            $updateData['updated_by'] = Auth::user()->id;

            EmployeesProjects::where('id', $linkageId)->update($updateData);

            //=====================================================      ADD MAIL
            if(Auth::user()->id != $originalData['employee_id']){
                $mailData = [
                    'link' => route('projects.details', ['id' => $project->id]) .'#requests',
                    'firstName' => $employee->first_name,
                    'project_name' => $project->name,
                    'currentUserId' => Auth::user()->id,
                    'module' => "Employee",
                    'updatedDetails' => explode(",", $updatedData),
                ];

                Mail::to($employee->email)->send(new Project($mailData, config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_UPDATE_BY_MANAGER')));
            }

            $message = 'Employee has been successfully updated';
        }else{
            //if an employee edits his own data and is not the manager
            $tojson = [];
            foreach ($updateData as $key => $val) {
                if ($originalData->$key !== $val) {
                    $tojson[$key] = $val;
                }
            }

            EmployeesProjects::where('id', $linkageId)->update([
                'approved_status' => config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'),
                'updated_by' => Auth::user()->id,
                'update_data' => json_encode($tojson, true),
            ]);

            // notify the managers of the request
            $mailData = [
                'link' => route('projects.details', ['id' => $project->id]) .'#requests',
                'requestor' => Auth::user()->first_name .' ' .Auth::user()->last_name,
                'project_name' => $project->name,
                'member' => $employee->first_name .' ' .$employee->last_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",
                'updatedDetails' => explode(",", $updatedData),
            ];

            Mail::to(Employees::getEmailOfManagers())->send(new Project($mailData, config('constants.MAIL_PROJECT_EMPLOYEE_LINKAGE_UPDATE_REQUEST')));

            $message = 'Request for linkage update has been sent';
        }
        
        Logs::createLog("Project", "Updated the linkage data of {$employee->first_name} {$employee->last_name} to {$project->name}.\n Updated Details:".str_replace(",", ", ", $updatedData));
        session(['pj_alert'=> $message]);

        return response()->json(['success' => true, 
                                    'message' => $message, 
                                    'update' => EmployeesProjects::getProjectMembersById($originalData['project_id']),
                                    'employee_id' => $employee->id,
                                    'employee_role' => $employee->roles
                                    ]
                                , 200);
    }

    /**
     * Link a project to the Software from Software details screen
     *
     * @param LinkProject $request
     * @return void
     */
    public function linkSoftwareToProject (LinkProject $request) {
        $data = $request->except('_token');

        $insertData = [
            'project_id' => $data['project_id'],
            'software_id' => $data['software_id'],
            'remarks' => $data['remarks'],
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ];

        //insert data
        ProjectSoftwares::create($insertData);
        $message = "Software was successfully linked";

        $projectData = Projects::where('id', $insertData['project_id'])->first();
        $softwareData = Softwares::where('id', $insertData['software_id'])->first();

        Logs::createLog('Project',"Linked the {$softwareData->software_name} to {$projectData->name}" );
        session(['ps_alert'=> $message]);
        return response()->json(['success' => true, 
                                    'message' => $message, 
                                    'update' => ProjectSoftwares::getLinkedSoftwareByProject($insertData['project_id']),
                                    'isManager' => Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')],
                                    200);
    }

    /**
     * Delete server in server list screen
     *
     * @param Request $request
     * @return void
     */
    public function deleteServer (Request $request) {
        //get server id
        $id = $request->get('id');

        //get server data for logs
        $server = Servers::where('id', $id)->first();
        if (empty($server)) {
            //return an error
            return response()->json([
                'success' => false,
                'error' => 'Error! Server does not exist.',
            ]);
        }

        //delete servers partitions
        ServersPartitions::where('server_id', $id)->delete();
        //delete server permanently in DB
        Servers::where('id', $id)->delete();


        Logs::createLog('Server', 'Deletion of ' .$server->server_name);

        //set success message in session
        session([
            'success' => true,
            'message' => 'Deletion of ' .$server->server_name . ' was successful!',
        ]);

        return response()->json([
            'success' => true,
        ]);
    }


    /**
     * Get Cities from selected province
     *
     * @param Request $requests
     * @return void
     */
    public function getCities(Request $request){
        $province = $request->get('province');

        return response()->json([
            'success' => true,
            'cities' => config("provinces_cities.PROVINCES_CITIES")[$province]
        ]);
    }

    /**
     * Cancel employee update
     *
     * @param Request $request
     * @return void
     */
    public function cancelEmployeeDetails(Request $request){
        $employeeId = $request->input('id');
        $message = '';
        $success = false;

        if(empty($employeeId)){
            $message = 'Invalid Request!';
        }else{
            $employee = Employees::where('id', $employeeId)
                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                ->first();
            if(empty($employee)){
                $message = 'Invalid Request!';
            } else {
                $message = 'The employee update has already been cancelled.';
                //check if employee has no linked laptops
                $success = true;
                    //changes
                    $arChanges = [
                            'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                            'update_data' => NULL,
                            'updated_by' => Auth::user()->id,
                        ];

                    // if new registration
                    Employees::where('id', $employee['id'])->update($arChanges);
                    //create logs
                    Logs::createLog("Employee", 'Cancel Update Employee Details Request');

                    //send mail to managers
                    $recipients = Employees::getEmailOfManagers();

                    $mailData = [
                        'employeeName' => $employee->first_name . " " . $employee->last_name,
                        'module' => "Employee",
                        'currentUserId' => Auth::user()->id,
                    ];

                    $this->sendMailForEmployeeUpdate($recipients, $mailData, config('constants.MAIL_EMPLOYEE_CANCEL_UPDATE'));
                    session(['success' => $success, 'message'=> $message]);
            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Cancel laptop registration
     *
     * @param Request $request
     * @return void
     */
    public function cancelLaptopRegister(Request $request){
        $laptopId = $request->input('id');
        $message = '';
        $success = false;

        if(empty($laptopId)){
            $message = 'Invalid Request!';
        }else{
            $laptop = Laptops::where('id', $laptopId)
                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_PENDING')])
                                ->first();
            if(empty($laptop)){
                $message = 'Invalid Request!';
            } else {
                $message = 'Laptop registration has already been cancelled.';
                //check if employee has no linked laptops
                $success = true;
                    //changes
                    $arChanges = [
                            'approved_status' => config('constants.CANCEL_REGIST'),
                            'updated_by' => Auth::user()->id,
                        ];

                    // if new registration
                    Laptops::where('id', $laptop->id)
                        ->update($arChanges);

                    $employee = Employees::where('id', $laptop->created_by)
                            ->first();

                    //create logs
                    Logs::createLog("Laptop", 'Cancel Laptop Registration Request');

                    //send mail to managers
                    $recipients = Employees::getEmailOfManagers();

                    $mailData = [
                        'employeeName' => $employee->first_name . " " . $employee->last_name,
                        'module' => "Employee",
                        'laptopDetails' => $laptop,
                        'currentUserId' => Auth::user()->id,
                    ];

                    $this->sendMailForLaptop($recipients, $mailData, config('constants.MAIL_LAPTOP_CANCEL_REGIST'));
                    session(['laptop_alert'=> $message]);
            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Cancel employee update
     *
     * @param Request $request
     * @return void
     */
    public function cancelLaptopUpdate(Request $request){
        $laptopId = $request->input('id');
        $message = '';
        $success = false;

        if(empty($laptopId)){
            $message = 'Invalid Request!';
        }else{
            $laptop = Laptops::where('id', $laptopId)
                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                ->first();
            if(empty($laptop)){
                $message = 'Invalid Request!';
            } else {
                $message = 'The laptop update has already been cancelled.';
                //check if employee has no linked laptops
                $success = true;
                    //changes
                    $arChanges = [
                            'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                            'update_data' => NULL,
                            'updated_by' => Auth::user()->id,
                        ];

                    // if new registration
                    Laptops::where('id', $laptop->id)
                        ->update($arChanges);

                    $employee = Employees::where('id', $laptop->updated_by)
                            ->first();

                    //create logs
                    Logs::createLog("Laptop", 'Cancel Laptop Update Request');

                    //send mail to managers
                    $recipients = Employees::getEmailOfManagers();

                    $mailData = [
                        'employeeName' => $employee->first_name . " " . $employee->last_name,
                        'module' => "Employee",
                        'laptopDetails' => $laptop,
                        'currentUserId' => Auth::user()->id,
                    ];

                    $this->sendMailForLaptop($recipients, $mailData, config('constants.MAIL_LAPTOP_CANCEL_UPDATE'));
                    session(['l_alert'=> $message]);
            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Cancel employee update
     *
     * @param Request $request
     * @return void
     */
    public function cancelEmployeeLaptopUpdate(Request $request){
        $id = $request->input('id');
        $message = '';
        $success = false;

        if(empty($id)){
            $message = 'Invalid Request!';
        }else{
            $employeeLaptop = EmployeesLaptops::where('id', $id)
                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                ->first();
            if(empty($employeeLaptop)){
                $message = 'Invalid Request!';
            } else {
                $message = 'The linkage update for employees and laptops has already been cancelled.';
                //check if employee has no linked laptops
                $success = true;
                //changes
                $arChanges = [
                        'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                        'update_data' => NULL,
                        'updated_by' => Auth::user()->id,
                    ];

                // if new registration
                EmployeesLaptops::where('id', $employeeLaptop->id)
                    ->update($arChanges);

                $employee = Employees::where('id', $employeeLaptop->updated_by)->first();

                $laptop = Laptops::where('id', $employeeLaptop->laptop_id)->first();

                //create logs
                Logs::createLog("Laptop", 'Cancel Linkage Update for Employee and Laptop Request');

                //send mail to managers
                $recipients = Employees::getEmailOfManagers();

                $mailData = [
                    'employeeName' => $employee->first_name . " " . $employee->last_name,
                    'module' => "Employee",
                    'laptopDetails' => $laptop,
                    'currentUserId' => Auth::user()->id,
                ];

                $this->sendMailForLaptop($recipients, $mailData, config('constants.MAIL_LAPTOP_CANCEL_LINK_UPDATE'));
                session(['l_alert'=> $message]);
            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Cancel employee update
     *
     * @param Request $request
     * @return void
     */
    public function cancelEmployeeLaptopLink(Request $request){
        $id = $request->input('id');
        $message = '';
        $success = false;

        if(empty($id)){
            $message = 'Invalid Request!';
        }else{
            $employeeLaptop = EmployeesLaptops::where('id', $id)
                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_PENDING')])
                                ->first();
            if(empty($employeeLaptop)){
                $message = 'Invalid Request!';
            } else {
                $message = 'The linkage for employees and laptops has already been cancelled.';
                //check if employee has no linked laptops
                $success = true;
                //changes
                $arChanges = [
                        'approved_status' => config('constants.CANCEL_LINK'),
                        'updated_by' => Auth::user()->id,
                    ];

                // if new registration
                EmployeesLaptops::where('id', $employeeLaptop->id)
                    ->update($arChanges);

                $employee = Employees::where('id', $employeeLaptop->updated_by)->first();

                $laptop = Laptops::where('id', $employeeLaptop->laptop_id)->first();

                //create logs
                Logs::createLog("Laptop", 'Cancel Linkage for Employee and Laptop Request');

                //send mail to managers
                $recipients = Employees::getEmailOfManagers();

                $mailData = [
                    'employeeName' => $employee->first_name . " " . $employee->last_name,
                    'module' => "Employee",
                    'laptopDetails' => $laptop,
                    'currentUserId' => Auth::user()->id,
                ];

                $this->sendMailForLaptop($recipients, $mailData, config('constants.MAIL_LAPTOP_CANCEL_LINK'));
                session(['l_alert'=> $message]);
            }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }
}
