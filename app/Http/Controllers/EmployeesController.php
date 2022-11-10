<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeesRequest;
use App\Mail\Employee;
use App\Models\Employees;
use App\Models\EmployeesLaptops;
use App\Models\EmployeesProjects;
use App\Models\Laptops;
use App\Models\Logs;
use App\Models\Projects;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\updateContactDetailsMail;
use Excel;
use App\Exports\EmployeesExport;


class EmployeesController extends Controller
{
    
    public function create($rejectCode = ""){
        $employee = '';
        if($rejectCode){
            $employee = Employees::where('reject_code', $rejectCode)
            ->where('approved_status', config('constants.APPROVED_STATUS_REJECTED'))
            ->where('active_status', 0)
            ->first();

            abort_if(empty($employee), 404);
        }

        return view('employees.regist')->with(['employee' => $employee]);
    }

    public function regist(EmployeesRequest $request){
        $request->validated();

        $insertData = $this->getEmployeeData($request);
        $insertData['roles'] = $this->getRoleBasedOnPosition($insertData['position']);
        
        if(isset($insertData['id'])){
            //update data only
            $id = $insertData['id'];
            unset($insertData['id']);
            unset($insertData['created_by']);

            $additionalData = [
                'approved_status' => config('constants.APPROVED_STATUS_PENDING'),
                'reasons' => NULL,
                'reject_code' => NULL,
            ];

            $insertData = array_merge($insertData, $additionalData);

            Employees::where('id', $id)
                        ->update($insertData);

        }else{
            //insert new entry
            $id = Employees::create($insertData)->id;
            //update created_by/updated_by
            Employees::where('id', $id)
                        ->update(['updated_by' => $id, 'created_by' => $id]);
        }

        //create logs
        Logs::createLog("Employee", 'Employee Registration');

        //send mail to managers
        $recipients = Employees::getEmailOfManagers();

        $mailData = [
            'link' => "/employees/{$id}/request",
            'currentUserId' => $id,
            'module' => "Employee",
        ];
        $this->sendMail($recipients, $mailData, config('constants.MAIL_NEW_REGISTRATION_REQUEST'));
        
        return redirect(route('employees.regist.complete'));
    }

    public function detail($id){
        $employeeDetails = Employees::where('id', $id)->first();

        abort_if(empty($employeeDetails), 404); //employee does not exist

        abort_if((!$employeeDetails->active_status && in_array($employeeDetails->approved_status, [config('constants.APPROVED_STATUS_REJECTED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]))
            || ($employeeDetails->active_status && in_array($employeeDetails->approved_status, [config('constants.APPROVED_STATUS_REJECTED'), config('constants.APPROVED_STATUS_PENDING')]))
            , 403); //invalid combination of approved_status and active_status

        //check if employee has pending update, or if employee's account is not yet activated
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE') &&
            (($employeeDetails->active_status && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'))
            || (!$employeeDetails->active_status && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')))){
            return redirect(route('employees.request', ['id' => $id]));
        }

        //check if allowed to edit
        $allowedToEdit = false;
        if((Auth::user()->id == $employeeDetails->id && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_APPROVED'))
                || in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')])){
            $allowedToEdit = true;
        }

        return view('employees.details')
                    ->with([
                        'allowedToEdit' => $allowedToEdit,
                        'readOnly' => true,
                        'detailOnly' => true,
                        'detailNote' => $this->getAccountStatus($employeeDetails),
                        'employee' => $employeeDetails,
                        'empLaptop' => EmployeesLaptops::getOwnedLaptopByEmployee($id),
                        'empProject' => EmployeesProjects::getProjectsByEmployee($id),
                        'laptopList' => Laptops::getLaptopDropdown(),
                        'projectList' => Projects::getProjectDropdownPerEmployee($id)
                    ]);
    }



    public function edit($id){
        //check if user is allow to access the edit page
        abort_if(Auth::user()->id != $id && !in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')]), 403);

        $employee = Employees::where('id', $id)->first();

        //check if employee has pending update, or if employee's account is not yet activated
        if(($employee->active_status && $employee->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'))
            || (!$employee->active_status && $employee->approved_status == config('constants.APPROVED_STATUS_PENDING'))){

            if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
                return redirect(route('employees.request', ['id' => $id]));
            }else{
                abort(403);
            }
        }

        return view('employees.edit')->with([
                                        'employee' => $employee,
                                        'manager_admin' => in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')])
                                    ]);

    }

    public function update(EmployeesRequest $request){
        $request->validated();

        $updateData = $this->getEmployeeData($request);
        $id = $updateData['id'];
        $originalData = Employees::where('id', $id)->first();
        unset($updateData['id']);
        unset($updateData['created_by']);
        unset($updateData['password']);

        //check logined employee role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            //save directly in DB in db
            Employees::where('id', $id)
                ->update($updateData);

            if(Auth::user()->id != $id){
                //notify the employee
                $mailData = [
                    'link' => route('employees.details', ['id' => $id]),  
                    'updater' => Auth::user()->first_name .' ' .Auth::user()->last_name,
                    'first_name' => $originalData->first_name,
                    'currentUserId' => Auth::user()->id,
                    'module' => "Employee",
                ];

                $this->sendMail($updateData['email'], $mailData, config('constants.MAIL_EMPLOYEE_UPDATE_BY_MANAGER'));
            }

            //format log
            $log = "";
            foreach($updateData as $key => $value){
                if($value != $originalData[$key] && !in_array($key, ['updated_by', 'password'])){
                    $log .= "{$key}: {$originalData[$key]} > {$value}, ";
                }
            }
            $log = rtrim($log, ", ");

            Logs::createLog("Employee", $log);

            if(Auth::user()->id != $id){
                return redirect(route('employees.details', ['id' => $id]));
            }else{
                return redirect(route('employees.update.complete')); 
            }

        }else{
            //if an employee edits his own data and is not the manager
            $json = [];
            foreach($updateData as $key => $value){
                if($value != $originalData[$key] && !in_array($key, ['updated_by', 'password'])){
                    $json[$key] = $value;
                }
            }
            Employees::where('id', $id)
                        ->update([
                            'updated_by' => Auth::user()->id,
                            'update_data' => json_encode($json, true),
                            'approved_status' => config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')
                        ]);

            //notify the managers of the request
            $mailData = [
                'link' => "/",  //update link
                'requestor' => Auth::user()->first_name .' ' .Auth::user()->last_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",
            ];

            $this->sendMail(Employees::getEmailOfManagers(), $mailData, config('constants.MAIL_EMPLOYEE_UPDATE_REQUEST'));
 
            Logs::createLog("Employee", "{$originalData->email}: Employee Details Update: " .json_encode($updateData, true));
            return redirect(route('employees.update.complete'));
        }
        
        
    }

    public function request($id){

        $employeeDetails = Employees::where('id', $id)->first();

        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE'), 403);   //can only be accessed by manager

        abort_if(empty($employeeDetails), 404); //employee does not exist

        //check if employee has pending request
        
        if($employeeDetails->active_status && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
            //display employee's update
            $updateData = json_decode($employeeDetails->update_data, true);
            if(!empty($updateData)){
                foreach($updateData as $key => $val){
                    $employeeDetails->$key = $val;
                }
            }
        }elseif(!(!$employeeDetails->active_status && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING'))){
            abort(404);
        }

        return view('employees.details')
        ->with([
            'allowedToEdit' => false,
            'readOnly' => true,
            'detailOnly' => false,
            'detailNote' => $this->getAccountStatus($employeeDetails),
            'showRejectCodeModal' => 1,
            'employee' => $employeeDetails,
            'empLaptop' => EmployeesLaptops::getOwnedLaptopByEmployee($id),
            'empProject' => EmployeesProjects::getProjectsByEmployee($id),
            'laptopList' => Laptops::getLaptopDropdown(),
            'projectList' => Projects::getProjectDropdownPerEmployee($id)
        ]);
    }

    public function store(Request $request){
        $id = $request->input('id');

        $error = $this->validateRequest($id);
        if($error){
            //id is not included in the request, show error page
            return view('error.requestError')
                        ->with([
                            'error' => $error
                        ]);
        }

        $employee = Employees::where('id',$id)->first();
        
        //if no error, update employee details
        if(!$employee->active_status && $employee->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            //if new registration
            Employees::where('id', $employee['id'])
                ->update([
                    'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                    'active_status' => 1,
                    'updated_by' => Auth::user()->id,
                    'approved_by' => Auth::user()->id,
                ]);

            //send mail
            $this->sendMail($employee->email, ['first_name' => $employee->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",], config('constants.MAIL_NEW_REGISTRATION_APPROVAL'));

            Logs::createLog("Employee", "Approved the account registration of {$employee->first_name} {$employee->last_name}.");
        
        }else{
            //update only
            $employeeUpdate = json_decode($employee->update_data, true);
            $employeeUpdate['updated_by'] = Auth::user()->id;
            $employeeUpdate['approved_by'] = Auth::user()->id;
            $employeeUpdate['update_data'] = NULL;
            $employeeUpdate['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            Employees::where('id', $employee['id'])->update($employeeUpdate);
            
            //send mail
            $this->sendMail($employee->email, ['first_name' => $employee->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",], config('constants.MAIL_EMPLOYEE_UPDATE_APPROVAL'));

            //logs
            Logs::createLog("Employee", "Approved the update details of {$employee->first_name} {$employee->last_name}");
        }

        return redirect(route('home'));
    }

    public function reject(Request $request){
        $id = $request->input('id');

        $error = $this->validateRequest($id);
        if($error){
            //id is not included in the request, show error page
            return view('error.requestError')
                        ->with([
                            'error' => $error
                        ]);
        }

        $employee = Employees::where('id',$id)->first();
        $reason = $request->input('reason');
        $this->removeNewLine($reason);

        if(!$employee->active_status && $employee->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            //if new registration
            $rejectCode = uniqid();
            Employees::where('id', $employee['id'])
                ->update([
                    'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                    'reasons' => $reason,
                    'reject_code' => $rejectCode,
                    'updated_by' => Auth::user()->id,
                ]);
            
            //send mail
            $mailData = [
                'first_name' => $employee->first_name,
                'reasons' => $reason,
                'link' => route('employees.create') ."/{$rejectCode}",
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",
            ];
            $this->sendMail($employee->email, $mailData, config('constants.MAIL_NEW_REGISTRATION_REJECTION'));

            Logs::createLog("Employee", "Rejected the employee registration of {$employee->first_name} {$employee->last_name} because of: {$reason}.");
        }else{
            Employees::where('id', $employee['id'])
                ->update([
                    'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                    'reasons' => $reason,
                    'update_data' => NULL,
                    'updated_by' => Auth::user()->id,
                ]);

            
            //send mail
            $mailData = [
                'first_name' => $employee->first_name,
                'reasons' => $reason,
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",
            ];
            $this->sendMail($employee->email, $mailData, config('constants.MAIL_EMPLOYEE_UPDATE_REJECTION'));
        
            //logs
            Logs::createLog("Employee", "Rejected the update details of {$employee->first_name} {$employee->last_name} because of: {$reason}");
        }

        return redirect(route('home'));
    }

    /**
     * Validates employee's request before updating/rejecting
     *
     * @param [type] $id
     * @return void
     */
    private function validateRequest($id){
        if(empty($id)){
            //id is not included in the request, show error page
            return 'Invalid request.';
        }
        
        $employee = Employees::where('id', $id)->first();
        
        if(empty($employee)){
            return 'Employee does not exists.';
        }

        //check if employee needs to be approved
        if(!(!$employee->active_status && $employee->approved_status == config('constants.APPROVED_STATUS_PENDING'))    //pending for new registration
            && !($employee->active_status && $employee->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'))){    //pending for update
                return 'Employee has no pending request.';
            }

        return ''; 
    }


    /**
     * note on employee's account status in detail screen
     *
     * @param Employees $employee
     * @return void
     */
    private function getAccountStatus($employee){
        $note = '';
        if(!$employee['active_status']){
            switch ($employee['approved_status']){
                case config('constants.APPROVED_STATUS_REJECTED'):     //rejected registration
                    $note = 'Account was rejected';
                    break;
                case config('constants.APPROVED_STATUS_PENDING'):     //pending registration
                    $note = 'Account is still pending for approval';
                    break;
                default:    //account has been deactivated 
                    $note = 'Account has been deactivated';
            }
        }else{
            if($employee['approved_status'] === config('constants.APPROVED_STATUS_REJECTED') || $employee['approved_status'] === config('constants.APPROVED_STATUS_PENDING')){
                $note = 'Account is invalid';
            }elseif($employee['approved_status'] === config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
                $note = 'Update is still pending';
            }
        }

        return $note;
    }

    /**
     * formats data for insert/update in employees table
     *
     * @param EmployeesRequest $request
     * @return array
     */
    private function getEmployeeData(EmployeesRequest $request){
        $data = $request->except(['_token', 'confirm_password', 'password']);
        $this->changeStringCase($data, ['email', 'password']);
        $data['password'] =  password_hash($request->input('password'), PASSWORD_BCRYPT);
        if(Auth::check()){
            //for data update
            $data['created_by'] = Auth::user()->id;
            $data['updated_by'] = Auth::user()->id;
        }
        return $data;
    }

    private function getRoleBasedOnPosition($position){
        if(in_array($position, [config('constants.POSITION_MANAGER_VALUE'), config('constants.POSITION_ASSSITANT_MANAGER_VALUE')])){
            return config('constants.MANAGER_ROLE_VALUE');
        }else{
            return config('constants.ENGINEER_ROLE_VALUE');
        }
    }

    /**
     * send email
     *
     * @param array $recipients
     * @param array $mailData
     * @param int $mailType
     * @return void
     */
    private function sendMail($recipients, $mailData, $mailType){
        if (!empty($recipients)) {
            Mail::to($recipients)->send(new Employee($mailData, $mailType));
        } 
        
    }

    /**
     * apply ucfirst to String vals
     *
     * @param array $data
     * @param array $except
     * @return void
     */
    private function changeStringCase(&$data, $except){
        foreach($data as $key => $datum){
            if(!in_array($key, $except) && preg_match('@[A-Za-z]@', $datum)){
                $words = explode(' ', $datum);
                $convertedWord = '';
                foreach($words as $idx => $word){
                    $convertedWord .= ucfirst(strtolower($word)) .' ';
                }
                $data[$key] = rtrim($convertedWord);
            }
        }
    }

    private function removeNewLine(&$string){
        str_replace(["\n\r", "\n", "\r"], ' ', $string);
    }

    public function index(Request $request){
        $employee_request = $this->getEmployee();

        return view('employees/list', ['employee_request' => $employee_request]);
    }

    private function getEmployee() {
        $employee = Employees::where(function($query) {
                        $query->where('active_status', 0)
                        ->where('approved_status',2);
                    })
                    ->orWhere(function($query) {
                        $query->where('active_status', 1)
                        ->whereIn('approved_status',[2,4]);
                    })
                ->orderBy('last_name', 'ASC')
                ->get();

        return $employee;
    }

    public function sendNotification(){
        // get all active employee
        // DB::enableQueryLog();
        $employee = Employees::select('email','first_name')
                    ->where('active_status',1)
                    ->where(function($query) {
                        $query->where('approved_status', 2)
                            ->orWhere('approved_status', 4);
                    })
                    ->where('email',"!=",'devjportal@awsys-i.com')->get();
        // $query = DB::getQueryLog();
        // dd($query);            
        foreach ($employee as $key => $detail) {
            $email = $detail['email'];
            //send mail
            $mailData = [
                'email' => $email,
                'first_name' => $detail['first_name'],
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",
            ];
            if (!empty($email)) {
                Mail::to($email)->send(new updateContactDetailsMail($mailData));
            } 
        }
        Logs::createLog("Employee", "Send notification to the active employee to remind them to update their contact details");
        return redirect()->route('employees')->with(['success' => 1, "message" => "Successfully sent notifications to all active employee."]);
    }

    public function download(Request $request) {
        
        Logs::createLog("Employee", "Downloaded list of employee");
        // determine excel type
        if (Auth::user()->roles != 3) {
            return (new EmployeesExport($request['searchInput'],$request['searchFilter'],$request['employeeStatus']))->download('DevJ Contact Details.xlsx');
        } else {
            return (new EmployeesExport($request['searchInput'],$request['searchFilter'],$request['employeeStatus']))->download('DevJ Contact Details.pdf');
        }

    }
}
