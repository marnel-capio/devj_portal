<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Employees;
use App\Models\Softwares;
use App\Models\EmployeesProjects;
use App\Models\EmployeesLaptops;
use App\Models\Laptops;
use App\Models\Logs;
use Carbon\Carbon;

class HomeController extends Controller
{   
    /**
     * Displays home page
     *
     * @return void
     */
    public function index(){
    	$employee_request = [];
    	if (Auth::user()->roles != 3) {
    		$employee_request = $this->getEmployeeRequest();
    	}
       
        $software_request = Softwares::getSoftwareRequest();
        return view('home', [   
                                'employee_request' => $employee_request,
                                'softwareRequest' => $software_request,
                                'laptopRequest' => Laptops::getLaptopRequest(),
                                'laptopLinkRequest' => EmployeesLaptops::getLinkLaptopRequest(),
                                'projectLinkRequest' => EmployeesProjects::getProjectEmployeeLinkRequest(),
                            ]);
    }
    /**
     * Display data
     *
     * @return void
     */
    public static function getHeaderData($id){
        $data = [];
        $data['date'] = Carbon::now()->toDayDateTimeString();
        
        $data['logs'] = Logs::getLogOfUser($id);
       
        return $data;
    }

    /**
     * Get all employee requests
     *
     * @return void
     */
    private function getEmployeeRequest() {
    	$employee = Employees::select('id','first_name','last_name','email','position','approved_status','reasons')
                    ->where(function($query) {
                        $query->where('active_status', 0)
                            ->whereIN('approved_status', [1,3]);
                    })
                    ->orWhere('approved_status', 4)
    				->orderBy('last_name', 'ASC')
    				->get();

        return $employee;
    }

}
