<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeesRequest;
use App\Mail\Employee;
use App\Models\Employees;
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
