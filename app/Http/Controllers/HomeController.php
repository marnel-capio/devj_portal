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
     */
    public function index(){
    	$employee_request = [];
    	if (Auth::user()->roles != 3) {
    		$employee_request = Employees::getEmployeeRequest();
    	}

        $employeeDetails = Employees::getActiveEmployeeDetails(Auth::user()->id);
        $software_request = Softwares::getSoftwareRequest();
        $laptop_request = Laptops::getLaptopRequest();
        $laptopLink_request = EmployeesLaptops::getLinkLaptopRequest();
        $projetLink_request = EmployeesProjects::getProjectEmployeeLinkRequest();

        $softwareUpdateRejected = array_filter($software_request, function($data) {
            return ($data['prev_updated_by'] != "" && $data['approved_status'] == config('constants.APPROVED_STATUS_APPROVED'));
        });

        $laptopUpdateRejected = array_filter($laptop_request, function($data) {
            return ($data['prev_updated_by'] != "" && $data['approved_status'] == config('constants.APPROVED_STATUS_APPROVED'));
        });

        $softwareRejected = array_filter($software_request, function($data) {
            return ($data['approved_status'] == config('constants.APPROVED_STATUS_REJECTED'));
        });

        $laptopRejected = array_filter($laptop_request, function($data) {
            return ($data['approved_status'] == config('constants.APPROVED_STATUS_REJECTED'));
        });

        $laptopLinkRejected = array_filter($laptopLink_request, function($data) {
            return ($data['approved_status'] == config('constants.APPROVED_STATUS_REJECTED'));
        });

        $projectLinkRejected = array_filter($projetLink_request, function($data) {
            return ($data['approved_status'] == config('constants.APPROVED_STATUS_REJECTED'));
        });


        $logs = Logs::getLogOfUser(Auth::user()->id);
        $today = Carbon::now();

        // Get the logged-in user's passport status
        $user = Employees::getPassportStatus(Auth::user());

        return view('home', [   
                                'user' => $user,
                                'employee_details' => is_array($employeeDetails) ? $employeeDetails[0] : "",
                                'employee_request' => $employee_request,
                                'softwareRequest' => $software_request,
                                'laptopRequest' => $laptop_request,
                                'laptopLinkRequest' => $laptopLink_request,
                                'projectLinkRequest' => $projetLink_request,
                                'softwareRejectedCount' => count($softwareRejected),
                                'laptopRejectedCount' => count($laptopRejected),
                                'laptopLinkRejectedCount' => count($laptopLinkRejected),
                                'projectLinkRejectedCount' => count($projectLinkRejected),
                                'softwareUpdateRejectedCount' => count($softwareUpdateRejected),
                                'laptopUpdateRejectedCount' => count($laptopUpdateRejected),
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
