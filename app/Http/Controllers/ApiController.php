<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePassword;
use App\Http\Requests\LinkLaptop;
use App\Http\Requests\LinkProject;
use App\Mail\Employee;
use App\Models\Employees;
use App\Models\EmployeesLaptops;
use App\Models\EmployeesProjects;
use App\Models\Laptops;
use App\Models\Logs;
use App\Models\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ApiController extends Controller
{
    public function changePassword(ChangePassword $request){
        
        $request->validated();
        $data = $request->only(['id', 'new_password']);

        //save new password
        Employees::where('id', $data['id'])
        ->update(['password' => password_hash($data['new_password'], PASSWORD_BCRYPT), 'updated_by' => Auth::user()->id]);

        Logs::createLog("Employee", "Updated password");

        return response()->json(['success' => true], 200);
    }

    public function linkLaptop(LinkLaptop $request){
        $request->validated();

        $data = $request->except(['_token', ]);

        $insertData = [
            'laptop_id' => $data['laptop_id'],
            'employee_id' => $data['employee_id'],
            'brought_home_flag' => $data['brought_home_flag'] ? 1 : 0,
            'vpn_flag' => $data['vpn_access_flag'] ? 1 : 0,
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
                ];
                $this->sendMailForEmployeeUpdate($employee->email, $mailData, config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_BY_MANAGER'));
            }
            $message = 'Added Successfully';
        }else{
            //if an employee edits his own data and is not the manager
            $insertData['approved_status'] = config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE');

            EmployeesLaptops::create($insertData);

            //notify the managers of the request
            $mailData = [
                'link' => "/",  //update link
                'requestor' => Auth::user()->first_name .' ' .Auth::user()->last_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",
            ];

            $this->sendMailForEmployeeUpdate(Employees::getEmailOfManagers(), $mailData, config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_REQUEST'));
            $message = 'Your request has been sent';
        }

        Logs::createLog("Employee", "Link {$employee->first_name} {$employee->last_name} to {$laptop->tag_number} laptop");

        return response()->json(['success' => true, 
                                    'message' => $message, 
                                    'update' => EmployeesLaptops::getOwnedLaptopByEmployee($data['employee_id'])]
                                , 200);
    }

    public function linkProject(LinkProject $request){
        $request->validated();

        //save data in db
        $data = $request->except(['_token', ]);

        $insertData = [
            'project_id' => $data['project_id'],
            'employee_id' => $data['employee_id'],
            'start_date' => $data['project_start'],
            'end_date' => $request->filled('project_end') ?: NULL,
            'project_role_type' => $data['project_role'],
            'onsite_flag' => $data['project_onsite'] ? 1 : 0,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ];

        $employee = Employees::where('id', $data['employee_id'])->first();
        $project = Projects::where('id', $data['project_id'])->first();

        //check logined employee role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            //save directly in DB in db
            $insertData['approved_status'] =  config('constants.APPROVED_STATUS_APPROVED');
            $insertData['approved_by'] = Auth::user()->id;        

            EmployeesProjects::create($insertData);

            if(Auth::user()->id != $data['employee_id']){
                $mailData = [
                    'link' => route('employees.details', ['id' => $employee->id]),
                    'first_name' => $employee->first_name,
                    'currentUserId' => Auth::user()->id,
                    'module' => "Employee",
                ];
                $this->sendMailForEmployeeUpdate($employee->email, $mailData, config('constants.MAIL_EMPLOYEE_PROJECT_LINK_BY_MANAGER'));
            }
            $message = 'Added Successfully';
        }else{
            //if an employee edits his own data and is not the manager
            $insertData['approved_status'] = config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE');

            EmployeesProjects::create($insertData);

            //notify the managers of the request
            $mailData = [
                'link' => "/",  //update link
                'requestor' => Auth::user()->first_name .' ' .Auth::user()->last_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",
            ];

            $this->sendMailForEmployeeUpdate(Employees::getEmailOfManagers(), $mailData, config('constants.MAIL_EMPLOYEE_PROJECT_LINK_REQUEST'));
            $message = 'Your request has been sent';
        }
        
        Logs::createLog("Employee", "Link {$employee->first_name} {$employee->last_name} to {$project->name}");

        $updatedData =  EmployeesProjects::getProjectsByEmployee($data['employee_id']);
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

    public function getEmployeeByFilter(Request $request){
        $searchFilter = [
            'keyword' => $request->get('keyword'),
            'filter' => $request->get('filter'),
            'status' => $request->get('status'),
        ];
        // DB::enableQueryLog();
        $employee = Employees::where(function($query) {
                        $query->where('active_status', 0)
                        ->where('approved_status',2);
                    })
                    ->orWhere(function($query) {
                        $query->where('active_status', 1)
                        ->whereIn('approved_status',[2,4]);
                    });

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

        if ($searchFilter['status'] != 1) {
            $status = $searchFilter['status'] == 2 ? 1 : 0;
            $employee = $employee->where('active_status', $status);
        }

        $employee = $employee->orderBy('last_name', 'ASC')
                ->get();

        return json_encode($employee);
    }

}
