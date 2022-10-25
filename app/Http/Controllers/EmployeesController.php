<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeesRequest;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    
    public function regist(EmployeesRequest $request){
        if($request->isMethod('GET')){
            return view('employees.regist');
        }
        dd($request);

    }
}
