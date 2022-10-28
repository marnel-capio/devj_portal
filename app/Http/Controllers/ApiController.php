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
        ->update(['password' => password_hash($data['new_password'], PASSWORD_BCRYPT)]);

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
            'surrender_flag' => $data['surrender_flag'] ? 1 : 0,
            'surrender_date' => $request->filled('surrender_date') ?: NULL,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ];

        //check logined employee role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            //save directly in DB in db
            $insertData['approved_status'] =  config('constants.APPROVED_STATUS_APPROVED');
            $insertData['approved_by'] = Auth::user()->id;            
            EmployeesLaptops::create($insertData);

            if(Auth::user()->id != $data['employee_id']){
                //if manager edits other employee's data, notify the employee
                //==============SAB BD no email, confirm muna if need

            }
        }else{
            //if an employee edits his own data and is not the manager
            $insertData['approved_status'] = config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE');

            EmployeesLaptops::create($insertData);

            //notify the managers of the request
            $mailData = [
                'link' => "/employees/{$data['employee_id']}/request",
            ];

            $this->sendMailForEmployeeUpdate(Employees::getEmailOfManagers(), $mailData, config('constants.MAIL_EMPLOYEE_LAPTOP_LINK_REQUEST'));

        }

        return response()->json(['success' => true], 200);
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

        //check logined employee role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            //save directly in DB in db
            $insertData['approved_status'] =  config('constants.APPROVED_STATUS_APPROVED');
            $insertData['approved_by'] = Auth::user()->id;        

            EmployeesProjects::create($insertData);

            if(Auth::user()->id != $data['employee_id']){
                //if manager edits other employee's data, notify the employee
                //==============SAB BD no email, confirm munaif need

            }
        }else{
            //if an employee edits his own data and is not the manager
            $insertData['approved_status'] = config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE');

            EmployeesProjects::create($insertData);

            //notify the managers of the request
            $mailData = [
                'link' => "/employees/{$data['employee_id']}/request",
            ];

            $this->sendMailForEmployeeUpdate(Employees::getEmailOfManagers(), $mailData, config('constants.MAIL_EMPLOYEE_PROJECT_LINK_REQUEST'));

        }

        
        return response()->json(['success' => true], 200);
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

}
