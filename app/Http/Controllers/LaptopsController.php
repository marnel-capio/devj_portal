<?php

namespace App\Http\Controllers;

use App\Exports\LaptopsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\LaptopsRequest;
use App\Mail\Laptops as MailLaptops;
use App\Models\Employees;
use App\Models\EmployeesLaptops;
use App\Models\Laptops;
use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LaptopsController extends Controller
{
    public function index(){

        $laptops = Laptops::getLaptopList();

        return view('laptops.index')->with(['laptopList' => $laptops]);
    }

    public function download(){

        Logs::createLog("Employee", "Laptop employee's laptop details");
        // determine excel type
        if (in_array(Auth::user()->roles, [config('constants.MANAGER_ROLE_VALUE'), config('constants.ADMIN_ROLE_VALUE')])) {
            return (new LaptopsExport())->download('DevJ Laptop Details.xlsx');
        } else {
            // return (new EmployeesExport($request['searchInput'],$request['searchFilter'],$request['employeeStatus'], 'pdf'))->download('DevJ Contact Details.pdf');
        }
        
    }

    public function create($rejectCode = ""){
        $laptop = '';
        if($rejectCode){
            $laptop = Laptops::where('reject_code', $rejectCode)
                ->where('approved_status', 3)
                ->where('status',1)
                ->first();
            abort_if(empty($laptop), 404);
        }

        return view('laptops.create')->with(['laptop' => $laptop]);
    }

    public function regist(LaptopsRequest $request){
        $request-> validated();
        
        $data = $request->except(['_token']);
        $data['status'] = 1;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;
        
        if(empty($data['id'])){
            //new registration
            unset($data['id']);
            if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
                //approve the registration, no email is sent
                $data['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');
                $data['approved_by'] = Auth::user()->id;

                $id = Laptops::create($data)->id;

            }else{
                //pending request, 
                $data['approved_status'] = config('constants.APPROVED_STATUS_PENDING');
                $id = Laptops::create($data)->id;

            }
        }else{
            //registration update
            $id = $data['id'];
            unset($data['id']);
            $data['approved_status'] = config('constants.APPROVED_STATUS_PENDING');
            $data['reject_code'] = NULL;
            $data['reasons'] = NULL;

            Laptops::where('id', $id)
                    ->update($data);
        }

        //create logs
        Logs::createLog("Laptop", 'Laptop Registration');

        //send mail to managers
        $recipients = Employees::getEmailOfManagers();

        $mailData = [
            'link' => "/laptops/{$id}/request",
            'currentUserId' => Auth::user()->id,
            'module' => "Laptop",
        ];

        Mail::to($recipients)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_NEW_REGISTRATION_REQUEST')));
        
        return redirect(route('laptops.regist.complete'));
    }

    public function details($id){

        $laptopDetails = Laptops::where('id', $id)
                                    ->whereIn('approved_status', [2,4])
                                    ->first();

        abort_if(empty($laptopDetails), 404);
        abort_if(!in_array($laptopDetails['approved_status'], [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]), 403);

    $linkageData = EmployeesLaptops::getLinkageData($id);

        if(in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')])){
            $employeeDropdown = Employees::getEmployeeNameList();
        }else{
            $employeeDropdown = [[
                'id' => Auth::user()->id,
                'employee_name' => Auth::user()->last_name .", " .Auth::user()->first_name,
            ]];
        }

        return view('laptops.details')->with(['detail' => $laptopDetails,
                                            'detailOnly' => true,
                                            'detailNote' => $this->getDetailNote($laptopDetails),
                                            'linkageData' => $linkageData,
                                            'history' => EmployeesLaptops::getLaptopHistory($id),
                                            'employeeDropdown' => $employeeDropdown,
                                        ]);

    }


    public function request($id){
        $laptopDetails = Laptops::where('id', $id)
            ->whereIn('approved_status', [3,4])
            ->first();

        abort_if(empty($laptopDetails), 404);

        if($laptopDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
            foreach(json_decode($laptopDetails->update_data, true) as $key => $value){
                $laptopDetails[$key] = $value;
            }
        }

        return view('laptops.details')->with([
            'detail' => $laptopDetails,
            'detailNote' => $this->getDetailNote($laptopDetails),
            'detailOnly' => false,
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

        $laptopDetails = Laptops::where('id', $id)->first();

        if($laptopDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            //approve the  data
            Laptops::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                        'updated_by' => Auth::user()->id,
                        'approved_by' => Auth::user()->id,
                    ]);

        //create logs
        Logs::createLog("Laptop", 'Laptop Registration Approval');

        //send mail to requestor
        $recipient = Employees::where('id', $laptopDetails->created_by)->first();

        $mailData = [
            'link' => "/laptops/{$id}",
            'firstName' => $recipient->first_name,
            'currentUserId' => Auth::user()->id,
            'module' => "Laptop",
        ];

        Mail::to($recipient->email)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_NEW_REGISTRATION_APPROVAL')));

        }else{
            $recipient = Employees::where('id', $laptopDetails->updated_by)->first();

            //save temporary data
            $update = json_decode($laptopDetails->update_data, true);
            $update['updated_by'] = Auth::user()->id;
            $update['approved_by'] = Auth::user()->id;
            $update['update_data'] = NULL;
            $update['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            Laptops::where('id', $id)
                    ->update($update);

            //create logs
            Logs::createLog("Laptop", 'Laptop Detail Update Approval');

            //send mail to requestor

            $mailData = [
                'link' => "/laptops/{$id}",
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
            ];

            Mail::to($recipient->email)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_DETAIL_UPDATE_APPROVAL')));
                
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

        $laptopDetails = Laptops::where('id', $id)->first();
        $reason = $request->input('reason');

        if($laptopDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            //approve the  data
            Laptops::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                        'reject_code' => uniqid(),
                        'reason' => $reason, 
                        'updated_by' => Auth::user()->id,
                        'approved_by' => Auth::user()->id,
                    ]);

        //create logs
        Logs::createLog("Laptop", 'Laptop Registration Rejection');

        //send mail to requestor
        $recipient = Employees::where('id', $laptopDetails->created_by)->first();

        $mailData = [
            'link' => "/laptops/{$id}",
            'reason' => $reason,
            'firstName' => $recipient->first_name,
            'currentUserId' => Auth::user()->id,
            'module' => "Laptop",
        ];

        Mail::to($recipient->email)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_NEW_REGISTRATION_REJECTION')));

        }else{
            $recipient = Employees::where('id', $laptopDetails->updated_by)->first();

            //save temporary data
            $update = json_decode($laptopDetails->update_data, true);
            $update['updated_by'] = Auth::user()->id;
            $update['approved_by'] = Auth::user()->id;
            $update['update_data'] = NULL;
            $update['approved_status'] = config('constants.APPROVED_STATUS_REJECTED');

            Laptops::where('id', $id)
                    ->update($update);

            //create logs
            Logs::createLog("Laptop", 'Laptop Detail Update Rejection');

            //send mail to requestor

            $mailData = [
                'reason' => $reason,
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
            ];

            Mail::to($recipient->email)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REJECTION')));
                
        }

        return redirect(route('home'));
    }

    /**
     * additional validation for approval or rejection
     *
     * @param [type] $details
     * @return void
     */
    private function validateRequest($id){
        if(empty($id)){
            //id is not included in the request, show error page
            return 'Invalid request.';
        }

        $detail = Laptops::where('id', $id)->first();

        if(empty($detail)){
            return 'laptop does not exists.';
        }

        //check if employee needs to be approved
        if($detail->approved_status != config('constants.APPROVED_STATUS_PENDING')    //pending for new registration
            && $detail->approved_status != config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){    //pending for update
            return 'Laptop has no pending request.';
        }

    return ''; 
    }

    private function getDetailNote($details){
        $note = '';
        if($details['status']){
            if($details['approved_status'] == config('constants.APPROVED_STATUS_PENDING')){
                $note = 'Registration is still pending';
            }elseif($details['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
                $note = 'Update is still pending';
            }
        }else{
            $note = 'Laptop is inactive';
        }

        return $note;
    }
}
