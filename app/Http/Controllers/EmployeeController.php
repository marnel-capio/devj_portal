<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use App\Models\Employees;

use PhpParser\Node\Stmt\Return_;
use App\Mail\updateContactDetailsMail;

use Excel;
use App\Exports\EmployeesExport;


class EmployeeController extends Controller
{
    public function index(Request $request){
    	$employee_request = $this->getEmployee();

        // $message = isset($request['message']) ? $request['message'] : "";
        // $success = isset($request['success']) ? $request['success'] : 0;

        return view('employee/list', ['employee_request' => $employee_request]);
    }

    private function getEmployee() {
    	$employee = Employees::where(function($query) {
                    $query->where('approved_status', '!=' ,3)
                    ->orWhere(function($query) {
                        $query->where('active_status', 0)
                                ->where('approved_status', '!=', 1);
                    });
                })
                ->orderBy('last_name', 'ASC')
                ->get();

        return $employee;
    }

    public function sendNotification(){
        // get all active employee
        $employee = Employees::select('email','first_name')
                    ->where('active_status',1)
                    ->orWhere(function($query) {
                        $query->where('approved_status', 2)
                            ->orWhere('approved_status', 4);
                    })
                    ->where('email',"!=",'devjpotal@awsys-i.com')->get();

        foreach ($employee as $key => $detail) {
            $email = $detail['email'];
            //send mail
            $mailData = [
                'email' => $email,
                'first_name' => $detail['first_name'],
            ];
            if (!empty($email)) {
                Mail::to($email)->send(new updateContactDetailsMail($mailData));
            } 
        }
        return redirect()->route('employees')->with(['success' => 1, "message" => "Successfully sent notifications to all active employee."]);
    }

    public function download(Request $request) {
        

        $type = "pdf";
        // determine excel type
        if (Auth::user()->roles != 3) {
            return (new EmployeesExport($request['searchInput'],$request['searchFilter']))->download('DevJ Contact Details.xlsx');
        } else {
            return (new EmployeesExport($request['searchInput'],$request['searchFilter']))->download('DevJ Contact Details.pdf');
        }

    }
}
