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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmployeesController extends Controller
{
    
    public function regist(EmployeesRequest $request){
        if($request->isMethod('GET')){
            return view('employees.regist');
        }

        $request->validated();

        $insertData = $this->getEmployeeData($request);

        $id = Employees::create($insertData)->id;
        //update created_by/updated_by
        Employees::where('id', $id)
                    ->update(['updated_by' => $id, 'created_by' => $id]);

        //send mail to managers
        $recipients = Employees::select('email')
                            ->where('roles', config('constants.MANAGER_ROLE_VALUE'))
                            ->where('active_status', 1)
                            ->get()
                            ->toArray();


        $mailData = [
            'link' => "/employees/{$id}/request",
        ];
        $this->sendMail($recipients, $mailData, config('constants.MAIL_NEW_REGISTRATION_REQUEST'));
        
        return redirect(route('employees.regist.complete'));
    }

    public function detail($id){
        // dd('detail');   
        $employeeDetails = Employees::where('id', $id)->first();
        if(empty($employeeDetails)){
            abort(404);
        }

        //check if allowed to edit
        $allowedToEdit = false;
        if(Auth::user()->id == $employeeDetails->id || in_array($employeeDetails->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')])){
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
        dd('edit page');
    }

    public function request($id){
        return view('employees.details')
        ->with([
            'readOnly' => true,
            'detailOnly' => false,
            // 'employee' => $employeeDetails,
            // 'project' => $projectInfo,
            // 'laptop' => $laptopInfo,
        ]);
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
            }elseif($employee['approved_status'] === config('constants.APPROVED_STATUS_PENDING-APPROVAL_FOR_UPDATE')){
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
