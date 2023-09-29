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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\updateContactDetailsMail;
use App\Exports\EmployeesExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class EmployeesController extends Controller
{
    /**
     * Display of employee registration
     *
     * @param string $rejectCode
     * @return void
     */
    public function create($rejectCode = ""){
        $employee = '';
        if($rejectCode){
            $employee = Employees::where('reject_code', $rejectCode)
            ->where('approved_status', config('constants.APPROVED_STATUS_REJECTED'))
            ->where('active_status', 0)
            ->first();


            abort_if(empty($employee), 404);
            
            // Get passport status
            $employee = Employees::getPassportStatus($employee);
        }

        return view('employees.regist')->with(['employee' => $employee]);
    }

    /**
     * Registration process for employee
     *
     * @param EmployeesRequest $request
     * @return void
     */
    public function regist(EmployeesRequest $request){
        $request->validated();

        $insertData = $this->getEmployeeData($request);
        
        $insertData['roles'] = $this->getRoleBasedOnPosition($insertData['position']);

        $insertData = $this->validatePassportStatusandInputs($insertData);

        $insertData = $this->validateAddressInputs($insertData);
        unset($insertData['copy_permanent_address']);

        // $objData = (object)$insertData;
        // abort_if(!$this->validatePassportStatusandInputs($objData), 404);
        
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
            'link' => route('employees.request', ['id' => $id]),
            'employeeName' => $insertData['last_name'] .', ' . $insertData['first_name'] .', ' . $insertData['name_suffix'],
            'position' => config('constants.POSITION_' .$insertData['position'] .'_NAME'),
            'currentUserId' => $id,
            'module' => "Employee",
        ];
        $this->sendMail($recipients, $mailData, config('constants.MAIL_NEW_REGISTRATION_REQUEST'));
        
        return redirect(route('employees.regist.complete'));
    }

    /**
     * Employee detail screen display
     *
     * @param [type] $id
     * @return void
     */
    public function detail($id){
        $employeeDetails = Employees::where('id', $id)->first();

        abort_if(empty($employeeDetails), 404); //employee does not exist

        //check if employee has pending update, or if employee's account is not yet activated
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE') &&
            ($employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')
            || (!$employeeDetails->active_status && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')))){
            return redirect(route('employees.request', ['id' => $id]));
        }

        abort_if((!$employeeDetails->active_status && in_array($employeeDetails->approved_status, [config('constants.APPROVED_STATUS_REJECTED'), config('constants.APPROVED_STATUS_PENDING')]))
            || ($employeeDetails->active_status && in_array($employeeDetails->approved_status, [config('constants.APPROVED_STATUS_REJECTED'), config('constants.APPROVED_STATUS_PENDING')]))
            , 403); //invalid combination of approved_status and active_status

        //check if allowed to edit
        $allowedToEdit = false;
        if((Auth::user()->id == $employeeDetails->id && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_APPROVED'))
                || (in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')]) && $employeeDetails->approved_status == config('constants.APPROVED_STATUS_APPROVED') )){
            $allowedToEdit = true;
        }
        
        // Get passport status
        $employeeDetails = Employees::getPassportStatus($employeeDetails);

        return view('employees.details')
                    ->with([
                        'allowedToEdit' => $allowedToEdit,
                        'readOnly' => true,
                        'detailOnly' => true,
                        'detailNote' => $this->getAccountStatus($employeeDetails),
                        'buTransferNote' => $employeeDetails->bu_transfer_flag ? "Employee has been assigned to " .config('constants.BU_LIST.' .$employeeDetails->bu_transfer_assignment) : "",
                        'employee' => $employeeDetails,
                        'empLaptop' => EmployeesLaptops::getOwnedLaptopByEmployee($id),
                        'empProject' => EmployeesProjects::getProjectsByEmployee($id),
                        'laptopList' => Laptops::getLaptopDropdown($id),
                        'projectList' => Projects::getProjectDropdownPerEmployee($id)
                    ]);
    }

    /**
     * Display of employee edit screen
     *
     * @param [type] $id
     * @return void
     */
    public function edit($id){
        $employee = Employees::where('id', $id)->first();

        abort_if(empty($employee), 404); //employee does not exist

        //check if user is allow to access the edit page
        abort_if(Auth::user()->id != $id && !in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')]), 403);

        //check if employee has pending update, or if employee's account is not yet activated
        if($employee->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')
            || (!$employee->active_status && $employee->approved_status == config('constants.APPROVED_STATUS_PENDING'))){
            if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
                return redirect(route('employees.request', ['id' => $id]));
            }else{
                abort(403);
            }
        }

        // Get passport status
        $employee = Employees::getPassportStatus($employee);

        abort_if((!$employee->active_status && $employee->approved_status == config('constants.APPROVED_STATUS_REJECTED'))
            || ($employee->active_status && in_array($employee->approved_status, [config('constants.APPROVED_STATUS_REJECTED'), config('constants.APPROVED_STATUS_PENDING')]))
            , 403); //invalid combination of approved_status and active_status

        return view('employees.edit')->with([
                                        'employee' => $employee,
                                        'isManager' => Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')
                                    ]);

    }

    /**
     * Employee update process
     *
     * @param EmployeesRequest $request
     * @return void
     */
    public function update(EmployeesRequest $request){
        $request->validated();

        $updateData = $this->getEmployeeData($request);
        $id = $updateData['id'];
        $originalData = Employees::where('id', $id)->first();

        $roleBasedOnPosition = $this->getRoleBasedOnPosition($updateData['position']);
        $rolebasedOnadminFlag = $request->input('is_admin', 0) ? config('constants.ADMIN_ROLE_VALUE') : config('constants.ENGINEER_ROLE_VALUE');
        if($roleBasedOnPosition == config('constants.MANAGER_ROLE_VALUE')){
            $updateData['roles'] = $roleBasedOnPosition;
        }else if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            $updateData['roles'] = $rolebasedOnadminFlag;
        }
       
        $updateData = $this->validatePassportStatusandInputs($updateData);
       
        $updateData = $this->validateAddressInputs($updateData);

        unset($updateData['id']);
        unset($updateData['created_by']);
        unset($updateData['password']);
        unset($updateData['copy_permanent_address']);

        //check logined employee role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            //save directly in DB in db
            $updateData['approved_by'] = Auth::user()->id;
            Employees::where('id', $id)
                ->update($updateData);

            if(Auth::user()->id != $id){
                //notify the employee
                $mailData = [
                    'link' => route('employees.details', ['id' => $id]),  
                    'updater' => Auth::user()->first_name .' ' .Auth::user()->last_name .' ' .Auth::user()->name_suffix,
                    'first_name' => $originalData->first_name,
                    'currentUserId' => Auth::user()->id,
                    'module' => "Employee",
                ];

                $this->sendMail($updateData['email'], $mailData, config('constants.MAIL_EMPLOYEE_UPDATE_BY_MANAGER'));
            }

            //format log
            $log = "Employee updated by manager: ";
            foreach($updateData as $key => $value){
                if($value != $originalData[$key] && !in_array($key, ['updated_by', 'password'])){
                    $log .= "{$key}: {$originalData[$key]} > {$value}, ";
                }
            }
            $log = rtrim($log, ", ");

            Logs::createLog("Employee", $log);

            if(Auth::user()->id == $id){
                return redirect(route('employees.details', ['id' => $id]))->with(['success' => 1, "message" => "Details are updated successfully."]);
            }else{
                return redirect(route('employees.update.complete')); 
            }

        }else{
            //if an admin or an engineer edits the employee details
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
                'link' => route('employees.request', ['id' => $id]),
                'requestor' => Auth::user()->first_name .' ' .Auth::user()->last_name .' ' .Auth::user()->name_suffix,
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",
                'employeeName' => $originalData->first_name .' ' .$originalData->last_name .' ' .$originalData->name_suffix,
            ];

            $this->sendMail(Employees::getEmailOfManagers(), $mailData, config('constants.MAIL_EMPLOYEE_UPDATE_REQUEST'));
 
            Logs::createLog("Employee", "{$originalData->email}: Employee Details Update: " .json_encode($json, true));
            return redirect(route('employees.update.complete'));
        }
    }

    /**
     * Display employee registration request or employee update request
     *
     * @param [type] $id
     * @return void
     */
    public function request($id){

        $employeeDetails = Employees::where('id', $id)->first();

        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE'), 403);   //can only be accessed by manager

        abort_if(empty($employeeDetails), 404); //employee does not exist

        // Get passport status

        $employeeDetails = Employees::getPassportStatus($employeeDetails);


        $detailNote = $this->getAccountStatus($employeeDetails);

        if($employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            $detailNote = 'Account is still pending for approval';
        }elseif($employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
            $detailNote = 'Update is still pending';
        }

        //check if employee has pending request

        if($employeeDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
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

        $requestor = Employees::selectRaw('concat(first_name, " ", last_name, " ", name_suffix) as requestor')->where('id', $employeeDetails->updated_by)->first();

        return view('employees.details')
        ->with([
            'allowedToEdit' => false,
            'readOnly' => true,
            'detailOnly' => false,
            'detailNote' => $detailNote,
            'showRejectCodeModal' => 1,
            'employee' => $employeeDetails,
            'requestor' => $requestor
        ]);
    }

    /**
     * Process the approval of employee request
     *
     * @param Request $request
     * @return void
     */
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
                    'reasons' => NULL,
                    'updated_by' => Auth::user()->id,
                    'approved_by' => Auth::user()->id,
                ]);

            //send mail
            $this->sendMail($employee->email, 
                            [
                                'first_name' => $employee->first_name,
                                'currentUserId' => Auth::user()->id,
                                'module' => "Employee",
                            ], 
                            config('constants.MAIL_NEW_REGISTRATION_APPROVAL'));

            Logs::createLog("Employee", "{$employee->first_name} {$employee->last_name} {$employee->name_suffix}'s account  has been approved.");
        
        }else{
            $ownAccount = true;
            //get requestor
            if($employee->id != $employee->updated_by){
                $ownAccount = false;
                $requestorData = Employees::where('id', $employee->updated_by)->first();
                $requestor = !empty($requestorData) ? $requestorData->first_name .' ' .$requestorData->last_name .' ' .$requestorData->name_suffix : 'unknown';
            }

            //update only
            $employeeUpdate = json_decode($employee->update_data, true);
            $employeeUpdate['updated_by'] = Auth::user()->id;
            $employeeUpdate['approved_by'] = Auth::user()->id;
            $employeeUpdate['update_data'] = NULL;
            $employeeUpdate['reasons'] = NULL;
            $employeeUpdate['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            Employees::where('id', $employee['id'])->update($employeeUpdate);
            
            //send mail
            $this->sendMail($employee->email, 
                                [   
                                    'first_name' => $employee->first_name,
                                    'currentUserId' => Auth::user()->id,
                                    'module' => "Employee",
                                    'ownAccount' => $ownAccount,
                                    'link' => route('employees.details', ['id' => $employee->id]),
                                    'updater' => !empty($requestor) ? $requestor : '',
                                ], 
                                config('constants.MAIL_EMPLOYEE_UPDATE_APPROVAL'));

            //logs
            Logs::createLog("Employee", "Approved the update details of {$employee->first_name} {$employee->last_name}");
        }

        return redirect(route('home'));
    }

    /**
     * Process the rejection of employee requests
     *
     * @param Request $request
     * @return void
     */
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

            Logs::createLog("Employee", "Rejected the employee registration of {$employee->first_name} {$employee->last_name} {$employee->name_suffix} because of: {$reason}.");
        }else{
            $ownAccount = true;
            //get requestor
            if($employee->id != $employee->updated_by){
                $ownAccount = false;
                $requestorData = Employees::where('id', $employee->updated_by)->first();
                $requestor = !empty($requestorData) ? $requestorData->first_name .' ' .$requestorData->last_name  .' ' .$requestorData->name_suffix : 'unknown';
            }

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
                'ownAccount' => $ownAccount,
                'updater' => !empty($requestor) ? $requestor : '',
            ];
            $this->sendMail($employee->email, $mailData, config('constants.MAIL_EMPLOYEE_UPDATE_REJECTION'));
        
            //logs
            Logs::createLog("Employee", "Rejected the update details of {$employee->first_name} {$employee->last_name} {$employee->name_suffix} because of: {$reason}");
        }

        return redirect(route('home'));
    }

     
    /*
    * Clear rejected update
    */
    public function clearRejectedUpdate() {

        Employees::where('created_by', Auth::user()->id)
                    ->update([
                        'updated_by' => Auth::user()->id,
                        'reasons' => null,
                    ]);
        //create logs
        Logs::createLog("Employee", 'Rejected Employee Update are all cleared.');
        return Redirect::back();
    }

    /**
     * Validates employee's request before updating/rejecting
     *
     * @param [type] $id
     * @return string
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
            && !$employee->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){    //pending for update
                return 'Employee has no pending request.';
            }

        return ''; 
    }


    /**
     * note on employee's account status in detail screen
     *
     * @param Employees $employee
     * @return string
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
        $data = $request->except(['_token', 'confirm_password', 'password', 'is_admin']);
        $this->changeStringCase($data, ['email', 'password', 'other_contact_info','passport_number','issuing_authority','place_of_issue','no_appointment_reason']);
        $data['password'] =  password_hash($request->input('password'), PASSWORD_BCRYPT);
        if(Auth::check()){
            //for data update
            $data['created_by'] = Auth::user()->id;
            $data['updated_by'] = Auth::user()->id;
        }
        return $data;
    }

    /**
     * Get role based on position
     *
     * @param [type] $position
     * @return int
     */
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

    /**
     * remove line breaks in string
     *
     * @param [type] $string
     * @return void
     */
    private function removeNewLine(&$string){
        str_replace(["\n\r", "\n", "\r"], ' ', $string);
    }

    /**
     * Display employee list
     *
     * @return void
     */
    public function index(){
        $employee_request = $this->getEmployee();

        return view('employees/list', ['employee_request' => $employee_request]);
    }

    /**
     * Get employee data for employee list screen
     *
     * @return void
     */
    private function getEmployee() {
        $query = Employees::whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);
        // do not display admin user
        $query->whereNot('email', config('constants.SYSTEM_EMAIL'));
        
        if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE')){
            $query->where('active_status', 1);
        }

        $employee = $query->orderBy('last_name', 'ASC')
                            ->get();

        return $employee;
    }
    
    /**
     * Validate combination of inputs and passport_status.
     * Accept fields only based on selected passport_status
     *
     * @param [array] $employee
     * @return array
     */
    private function validatePassportStatusandInputs($employee) {

        // Accept fields only based on selected passport_status
        if($employee['passport_status'] == config('constants.PASSPORT_STATUS_WITH_PASSPORT_VALUE')) {
            $employee['date_of_appointment'] = null;        // 2
            $employee['no_appointment_reason'] = null;      // 3
            $employee['date_of_delivery'] = null;           // 4
        } else if($employee['passport_status'] == config('constants.PASSPORT_STATUS_WITH_APPOINTMENT_VALUE')) {
            $employee['passport_number'] = null;            // 1
            $employee['date_of_issue'] = null;              // 1
            $employee['issuing_authority'] = null;          // 1
            $employee['passport_type'] = null;              // 1
            $employee['passport_expiration_date'] = null;   // 1
            $employee['place_of_issue'] = null;             // 1
            $employee['no_appointment_reason'] = null;      // 3
            $employee['date_of_delivery'] = null;           // 4
        } else if($employee['passport_status'] == config('constants.PASSPORT_STATUS_WITHOUT_PASSPORT_VALUE')) {
            $employee['passport_number'] = null;            // 1
            $employee['date_of_issue'] = null;              // 1
            $employee['issuing_authority'] = null;          // 1
            $employee['passport_type'] = null;              // 1
            $employee['passport_expiration_date'] = null;   // 1
            $employee['place_of_issue'] = null;             // 1
            $employee['date_of_appointment'] = null;        // 2
            $employee['date_of_delivery'] = null;           // 4
        } else if($employee['passport_status'] == config('constants.PASSPORT_STATUS_WAITING_FOR_DELIVERY_VALUE')) {
            $employee['passport_number'] = null;            // 1
            $employee['date_of_issue'] = null;              // 1
            $employee['issuing_authority'] = null;          // 1
            $employee['passport_type'] = null;              // 1
            $employee['passport_expiration_date'] = null;   // 1
            $employee['place_of_issue'] = null;             // 1
            $employee['date_of_appointment'] = null;        // 2
            $employee['no_appointment_reason'] = null;      // 3
        }
        
        return $employee;
    }
    
    /**
     * Accept fields from permanent address to current address
     *
     * @param [array] $employee
     * @return array
     */
    private function validateAddressInputs($employee) {

        // Accept fields only based on selected passport_status
        if(isset($employee['copy_permanent_address'])) {
            $employee['current_address_street'] = $employee['permanent_address_street'];
            $employee['current_address_city'] = $employee['permanent_address_city'];
            $employee['current_address_province'] = $employee['permanent_address_province'];
            $employee['current_address_postalcode'] = $employee['permanent_address_postalcode'];
        } 
        
        return $employee;
    }
    

    /**
     * Send notification to all active employee
     *
     * @return void
     */
    public function sendNotification(){
        // get all active employee
        $employee = Employees::select('id', 'email','first_name')
                    ->where('active_status',1)
                    ->where(function($query) {
                        $query->where('approved_status', config('constants.APPROVED_STATUS_APPROVED'))
                            ->orWhere('approved_status', config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'));
                    })
                    ->where('email',"!=",config('constants.SYSTEM_EMAIL'))->get();

        foreach ($employee as $key => $detail) {
            $email = $detail['email'];
            //send mail
            $mailData = [
                'email' => $email,
                'first_name' => $detail['first_name'],
                'currentUserId' => Auth::user()->id,
                'module' => "Employee",
                'detailLink' => route('employees.details', ['id' => $detail['id']]),
            ];
            if (!empty($email)) {
                Mail::to($email)->send(new updateContactDetailsMail($mailData));
            } 
        }
        Logs::createLog("Employee", "Send notification to the active employee to remind them to update their contact details");

        return response()
        ->json( ['success' => true, 
                'message' => config('constants.SEND_NOTIFICATION_MESSAGE_SUCCESS')],
                200);
    }

    /**
     * Download employee list
     *
     * @param Request $request
     * @return void
     */
    public function download(Request $request) {
        
        Logs::createLog("Employee", "Downloaded list of employee");
        // determine excel type
        if (Auth::user()->roles != 3) {
            return (new EmployeesExport($request['searchInput'],$request['searchFilter'],$request['employeeStatus'],$request['passportStatus'],))
            ->download('DevJ Contact Details.xlsx');
        } else {
            return (new EmployeesExport($request['searchInput'],$request['searchFilter'],$request['employeeStatus'],$request['passportStatus'], 'pdf'))
            ->download('DevJ Contact Details.pdf')
            ;
        }

    }

    
    /**
     * Get the passport status based on existing passport data
     *
     * @param [type] $employee
     * @return array
     */
    public static function getPassportStatus($employee) {
        return Employees::getPassportStatus($employee);

    }

}
