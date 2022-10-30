<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeesRequest;
use App\Mail\Employee;
use App\Models\Employees;
use App\Models\EmployeesLaptops;
use App\Models\EmployeesProjects;
use App\Models\Laptops;
use App\Models\Projects;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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

        //send mail to managers
        $recipients = Employees::getEmailOfManagers();

        $mailData = [
            'link' => "/employees/{$id}/request",
        ];
        $this->sendMail($recipients, $mailData, config('constants.MAIL_NEW_REGISTRATION_REQUEST'));
        
        return redirect(route('employees.regist.complete'));
    }

    public function detail($id){
        $employeeDetails = Employees::where('id', $id)->first();

        abort_if(empty($employeeDetails), 404); //employee does not exist

        //check if employee has pending update, or if employee's account is not yet activated
        if(($employeeDetails->active_status && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'))
            || (!$employeeDetails->active_status && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING'))){
            return redirect(route('employees.request', ['id' => $id]));
        }

        //check if allowed to edit
        $allowedToEdit = false;
        if((Auth::user()->id == $employeeDetails->id && $employeeDetails->approved_status = config('constants.APPROVED_STATUS_APPROVED'))
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
            return redirect(route('employees.request', ['id' => $id]));
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
        unset($updateData['id']);
        unset($updateData['created_by']);

        //check logined employee role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            //save directly in DB in db
            Employees::where('id', $id)
                ->update($updateData);

            //notify the employee
            $mailData = [
                'link' => route('employees.details', ['id' => $id]),  
                'updater' => Auth::user()->first_name .' ' .Auth::user()->last_name,
            ];

            $this->sendMailForEmployeeUpdate($updateData['email'], $mailData, config('constants.MAIL_EMPLOYEE_UPDATE_BY_MANAGER'));
 
        }else{
            //if an employee edits his own data and is not the manager
            Employees::where('id', $id)
                        ->update([
                            'updated_by' => Auth::user()->id,
                            'update_data' => json_encode($updateData, true),
                            'approved_status' => config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')
                        ]);

            //notify the managers of the request
            $mailData = [
                'link' => "/",  //update link
                'requestor' => Auth::user()->first_name .' ' .Auth::user()->last_name,
            ];

            $this->sendMailForEmployeeUpdate(Employees::getEmailOfManagers(), $mailData, config('constants.MAIL_EMPLOYEE_UPDATE_REQUEST'));
 
        }
        
        return redirect(route('employees.regist.complete'));    //check if need palitan
    }

    public function request($id){

        $employeeDetails = Employees::where('id', $id)->first();

        abort_if(empty($employeeDetails), 404); //employee does not exist

        //check if employee has pending request
        
        if($employeeDetails->active_status && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
            //display employee's update
            $updateData = json_decode($employeeDetails->update_data, true);
            foreach($updateData as $key => $val){
                $employeeDetails->$key = $val;
            }
        }elseif(!(!$employeeDetails->active_status && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING'))){
            abort(404);
        }

        return view('employees.details')
        ->with([
            'allowedToEdit' => false,
            'readOnly' => true,
            'detailOnly' => false,
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
                ]);

            //send mail
            $this->sendMail($employee->email, ['first_name' => $employee->first_name], config('constants.MAIL_NEW_REGISTRATION_APPROVAL'));

        }else{
            //update only
            $employeeUpdate = json_decode($employee->update_data, true);
            $employeeUpdate['updated_by'] = Auth::user()->id;
            $employeeUpdate['update_data'] = NULL;
            $employeeUpdate['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            Employees::where('id', $employee['id'])->update($employeeUpdate);
            
            //send mail
            $this->sendMail($employee->email, ['first_name' => $employee->first_name], config('constants.MAIL_EMPLOYEE_UPDATE_APPROVAL'));
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

        if(!$employee->active_status && $employee->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            //if new registration
            $rejectCode = $this->generateRejectCode();
            Employees::where('id', $employee['id'])
                ->update([
                    'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                    'reasons' => $request->input('reason'),
                    'reject_code' => $rejectCode,
                    'updated_by' => Auth::user()->id,
                ]);
            
            //send mail
            $mailData = [
                'first_name' => $employee->first_name,
                'reasons' => $request->input('reason'),
                'link' => route('employees.create') ."/{$rejectCode}",
            ];
            $this->sendMail($employee->email, $mailData, config('constants.MAIL_NEW_REGISTRATION_REJECTION'));

        }else{
            Employees::where('id', $employee['id'])
                ->update([
                    'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                    'reasons' => $request->input('reason'),
                    'update_data' => NULL,
                    'updated_by' => Auth::user()->id,
                ]);

            
            //send mail
            $mailData = [
                'first_name' => $employee->first_name,
                'reasons' => $request->input('reason'),
            ];
            $this->sendMail($employee->email, ['first_name' => $employee->first_name], config('constants.MAIL_EMPLOYEE_UPDATE_REJECTION'));
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

    private function generateRejectCode(){
        $length = 8; 
        $sets = [];
        $sets[] = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $sets[] = str_split('abcdefghijklmnopqrstuvwxyz');
        $sets[] = str_split('0123456789');
        $code = '';
        
        //get 1 character from each set
        foreach($sets as $set){
            $code .= $set[array_rand($set)];
        }

        while(strlen($code) < $length){
            $randomSet = $sets[array_rand($sets)];
            $code .= $randomSet[array_rand($randomSet)];
        }

        return str_shuffle($code);
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
            return false;
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

    /**
     * send email
     *
     * @param array $recipients
     * @param array $mailData
     * @param int $mailType
     * @return void
     */
    private function sendMail($recipients, $mailData, $mailType){
        Mail::to($recipients)->send(new Employee($mailData, $mailType));
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

}
