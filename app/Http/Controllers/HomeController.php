<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employees;
use App\Models\EmployeesLaptops;
use App\Models\Laptops;
use PhpParser\Node\Stmt\Return_;

class HomeController extends Controller
{
    public function index(){
    	$employee_request = [];
    	if (Auth::user()->roles != 3) {
    		$employee_request = $this->getEmployeeRequest();
    	}

        return view('home', [   
                                'employee_request' => $employee_request,
                                'laptopRequest' => Laptops::getLaptopRequest(),
                                'laptopLinkRequest' => EmployeesLaptops::getLinkLaptopRequest(),
                            ]);
    }

    private function getEmployeeRequest() {
    	$employee = Employees::select('id','first_name','last_name','email','position','approved_status')
                    ->where(function($query) {
                        $query->where('active_status', 0)
                            ->where('approved_status', 3);
                    })
                    ->orWhere('approved_status', 4)
    				->orderBy('last_name', 'ASC')
    				->get();

        return $employee;
    }
}
