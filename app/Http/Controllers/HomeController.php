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
    		$employee_request = Employees::getEmployeeRequest();
    	}
       
        $software_request = Softwares::getSoftwareRequest();
        $logs = Logs::getLogOfUser(Auth::user()->id);
        $today = Carbon::now();

        // Get the logged-in user's passport status
        $user = Employees::getPassportStatus(Auth::user());

        return view('home', [   
                                'user' => $user,
                                'employee_request' => $employee_request,
                                'softwareRequest' => $software_request,
                                'laptopRequest' => Laptops::getLaptopRequest(),
                                'laptopLinkRequest' => EmployeesLaptops::getLinkLaptopRequest(),
                                'projectLinkRequest' => EmployeesProjects::getProjectEmployeeLinkRequest(),
                                
                                'date' => Carbon::now()->toDayDateTimeString(),
                                'logs' => $logs,
                            ]);
    }

    public static function getTimePassed($time) {
        $time = new Carbon($time);
        return $time->diffForHumans(Carbon::now(),true);
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
