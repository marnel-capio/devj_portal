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
use Illuminate\Support\Facades\Redirect;

class LaptopsController extends Controller
{
    const LAPTOP_REQUEST = 1;
    const LAPTOP_LINK_REQUEST = 2;

    public function index(){

        $laptops = Laptops::getLaptopList();

        return view('laptops.index')->with(['laptopList' => $laptops]);
    }

    public function download(){

        Logs::createLog("Laptop", "Downloaded employee's laptop details");
        // determine file type
        if (in_array(Auth::user()->roles, [config('constants.MANAGER_ROLE_VALUE'), config('constants.ADMIN_ROLE_VALUE')])) {
            return (new LaptopsExport())->download('DevJ Laptop Details.xlsx');
        } else {
            return (new LaptopsExport(true))->download('DevJ Laptop Details.pdf');
        }
    }

    public function create($rejectCode = ""){
        $laptop = '';
        if($rejectCode){
            $laptop = Laptops::select(
                                    'id',
                                    'approved_status',
                                    'peza_form_number',
                                    'peza_permit_number',
                                    'tag_number',
                                    'laptop_make',
                                    'laptop_model',
                                    'laptop_cpu',
                                    'laptop_clock_speed',
                                    'laptop_ram',
                                    'remarks',
                                    'status'
                                )
                ->where('reject_code', $rejectCode)
                ->where('approved_status', config('constants.APPROVED_STATUS_REJECTED'))
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
            $data['updated_by'] = Auth::user()->id;

            Laptops::where('id', $id)
                    ->update($data);
        }

        //create logs
        Logs::createLog("Laptop", 'Laptop Registration');

        if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE')){
            //send mail to managers
            $recipients = Employees::getEmailOfManagers();

            $mailData = [
                'link' => "/laptops/{$id}/request",
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
            ];

            Mail::to($recipients)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_NEW_REGISTRATION_REQUEST')));
        }

        return redirect(route('laptops.regist.complete'));
    }

    public function details($id){
        $showLinkBtn = true;
        $laptopDetails = Laptops::select(
                                        'id',
                                        'approved_status',
                                        'peza_form_number',
                                        'peza_permit_number',
                                        'tag_number',
                                        'laptop_make',
                                        'laptop_model',
                                        'laptop_cpu',
                                        'laptop_clock_speed',
                                        'laptop_ram',
                                        'remarks',
                                        'status'
                                    )
                                    ->where('id', $id)
                                    ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'),config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                    ->first();

        abort_if(empty($laptopDetails), 404);

        if(!$laptopDetails->status){
            $showLinkBtn = false;
        }
        
        $employeeDropdown = [];
        if(in_array(Auth::user()->roles, [config('constants.ADMIN_ROLE_VALUE'), config('constants.MANAGER_ROLE_VALUE')])){
            $employeeDropdown = Employees::getEmployeeNameListForLaptopDropdown($id);
        }else{
            //check if employee has pending request
            $currentUserRequest = EmployeesLaptops::where('approved_status', config('constants.APPROVED_STATUS_PENDING'))
                                                ->where('laptop_id', $id)
                                                ->where('employee_id', Auth::user()->id)
                                                ->first();

            if(empty($currentUserRequest)){
                $employeeDropdown = [[
                    'id' => Auth::user()->id,
                    'employee_name' => Auth::user()->last_name .", " .Auth::user()->first_name,
                ]];
            }else{
                $showLinkBtn = false;
            }
        }

        $linkageData = EmployeesLaptops::getLinkageData($id);
        if(empty($linkageData)){
            //get new linkage request
            $linkageRequest = EmployeesLaptops::getLinkRequestByLaptop($id, config('constants.APPROVED_STATUS_PENDING'));
        }else{
            //get linkage update requests
            $linkageRequest = EmployeesLaptops::getLinkRequestByLaptop($id, config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'));

            //apply update to data
            foreach($linkageRequest as $idx => &$data){
                if(!empty($data['update_data'])){
                    foreach (json_decode($data['update_data'], true) as $key => $value){
                        if(strpos($key, '_flag') !== FALSE){
                            $value = $value ? 'Y' : 'N';
                        }
                        $data[$key] = $value;
                    }
                }
            }
        }

        return view('laptops.details')->with(['detail' => $laptopDetails,
                                            'detailOnly' => true,
                                            'detailNote' => $this->getDetailNote($laptopDetails),
                                            'linkageData' => $linkageData,
                                            'linkageRequest' => $linkageRequest,
                                            'history' => EmployeesLaptops::getLaptopHistory($id),
                                            'employeeDropdown' => $employeeDropdown,
                                            'showLinkBtn' => $showLinkBtn,
                                        ]);
    }


    public function request($id){
        $laptopDetails = Laptops::where('id', $id)
            ->whereIn('approved_status', [3,4])
            ->first();

        abort_if(empty($laptopDetails), 404);
        $detailNote = $this->getDetailNote($laptopDetails);

        $requestor = Employees::selectRaw('concat(first_name, " ", last_name) as requestor')
        ->where('id', $laptopDetails->updated_by)
        ->first();

        if($laptopDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
            $updateData = json_decode($laptopDetails->update_data, true);
            if(!is_null($updateData)){
                foreach($updateData as $key => $value){
                    $laptopDetails[$key] = $value;
                }
            }
        }

        return view('laptops.details')->with([
            'detail' => $laptopDetails,
            'requestor' => $requestor,
            'detailNote' => $detailNote,
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
                'link' => route('laptops.details', ['id' => $id]),
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
                'tagNumber' => $laptopDetails->tag_number,
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
                'tagNumber' => $laptopDetails->tag_number,
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
        $rejectCode = uniqid();

        if($laptopDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            //approve the  data
            Laptops::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                        'reject_code' => $rejectCode,
                        'reasons' => $reason, 
                        'updated_by' => Auth::user()->id,
                        'approved_by' => Auth::user()->id,
                    ]);

            //create logs
            Logs::createLog("Laptop", 'Laptop Registration Rejection');

            //send mail to requestor
            $recipient = Employees::where('id', $laptopDetails->created_by)->first();

            $mailData = [
                'link' => route('laptops.create') . '/' .$rejectCode,
                'reason' => $reason,
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
                'tagNumber' => $laptopDetails->tag_number,
            ];

            Mail::to($recipient->email)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_NEW_REGISTRATION_REJECTION')));

        }else{
            $recipient = Employees::where('id', $laptopDetails->updated_by)->first();

            Laptops::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                        'reasons' => $reason,
                        'update_data' => NULL,
                        'updated_by' => Auth::user()->id,
                        'approved_by' => Auth::user()->id,
                    ]);

            //create logs
            Logs::createLog("Laptop", 'Laptop Detail Update Rejection');

            //send mail to requestor

            $mailData = [
                'reason' => $reason,
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
                'tagNumber' => $laptopDetails->tag_number,
            ];

            Mail::to($recipient->email)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_DETAIL_UPDATE_REJECTION')));
                
        }

        return redirect(route('home'));
    }

    public function storeLinkage(Request $request){
        $id = $request->input('id');

        $error = $this->validateRequest($id, self::LAPTOP_LINK_REQUEST);
        if($error){
            //id is not included in the request, show error page
            return view('error.requestError')
                        ->with([
                            'error' => $error
                        ]);
        }

        $laptopLinkDetails = EmployeesLaptops::where('id', $id)->first();

        //get laptop data
        $laptopData = Laptops::where('id', $laptopLinkDetails->laptop_id)->first();
        //get mail recipient
        $recipient = Employees::where('id', $laptopLinkDetails->employee_id)->first();
        //get requestor
        if($laptopLinkDetails->employee_id == $laptopLinkDetails->updated_by){
            $requestor = $recipient;
        }else{
            $requestor = Employees::where('id', $laptopLinkDetails->updated_by)->first();
        }

        if($laptopLinkDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            //approve the  data
            EmployeesLaptops::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                        'updated_by' => Auth::user()->id,
                        'approved_by' => Auth::user()->id,
                    ]);

            //create logs
            Logs::createLog("Laptop", 'Laptop Linkage Request Approval');

            $mailData = [
                'link' => route('laptops.details', ['id' => $laptopLinkDetails->laptop_id]),
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
                'requestor' => !empty($requestor) ? $requestor->first_name .' ' .$requestor->last_name : 'unknown',
                'assignee' => $recipient->first_name .' ' .$recipient->last_name,
                'tagNumber' => $laptopData->tag_number,
            ];

            Mail::to($recipient->email)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_APPROVAL')));

            $alert = 'Successfully approved the laptop linkage.';
            //reject other new linkage request
            $this->rejectOtherLinkageRequest($laptopLinkDetails['laptop_id']);

        }else{
            //save temporary data
            $update = json_decode($laptopLinkDetails->update_data, true);
            $update['updated_by'] = Auth::user()->id;
            $update['approved_by'] = Auth::user()->id;
            $update['update_data'] = NULL;
            $update['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            EmployeesLaptops::where('id', $id)
                    ->update($update);

            //create logs
            Logs::createLog("Laptop", 'Laptop Linkage Detail Update Approval');

            //send mail to requestor
            $mailData = [
                'link' => route('laptops.details', ['id' => $laptopLinkDetails->laptop_id]),
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
                'requestor' => !empty($requestor) ? $requestor->first_name .' ' .$requestor->last_name : 'unknown',
                'assignee' => $recipient->first_name .' ' .$recipient->last_name,
                'tagNumber' => $laptopData->tag_number,
            ];

            Mail::to($recipient->email)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_APPROVAL')));

            $alert = 'Successfully approved the laptop linkage detail update.';
        }

        session(['lla_alert'=> $alert]);
        return Redirect::back();
    }

    private function rejectOtherLinkageRequest($laptop_id){
        $pendingApproval = EmployeesLaptops::getLinkRequestByLaptop($laptop_id, config('constants.APPROVED_STATUS_PENDING'));
        if(!empty($pendingApproval)){
            $reason = 'Laptop has been assigned to other employee';
            $pendingIds = array_column($pendingApproval, 'id');
            EmployeesLaptops::whereIn('id', $pendingIds)
                                ->update([
                                    'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                                    'approved_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id,
                                    'reasons' => $reason,
                                ]);
    
            Logs::createLog('Laptop', 'Laptop Linkage Request Rejection');
    
            //send mail
            foreach($pendingApproval as $request => $data){
                $mailData = [
                    'link' => route('laptops.details', ['id' => $laptop_id]),
                    'reason' => $reason,
                    'firstName' => $data['first_name'],
                    'currentUserId' => Auth::user()->id,
                    'module' => "Laptop",
                    'requestor' => $data['requestor_name'],
                    'assignee' => $data['assignee_name'],
                    'tagNumber' => $data['tag_number']
                ];
                Mail::to($data['email'])->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REJECTION')));
            }
        }
    }


    public function rejectLinkage(Request $request){
        $id = $request->input('id');

        $error = $this->validateRequest($id, self::LAPTOP_LINK_REQUEST);
        if($error){
            //id is not included in the request, show error page
            return view('error.requestError')
                        ->with([
                            'error' => $error
                        ]);
        }

        $laptopLinkDetails = EmployeesLaptops::where('id', $id)->first();
        $reason = $request->input('reason');

        //get laptop data
        $laptopData = Laptops::where('id', $laptopLinkDetails->laptop_id)->first();
        //get mail recipient
        $recipient = Employees::where('id', $laptopLinkDetails->employee_id)->first();
        //get requestor
        if($laptopLinkDetails->employee_id == $laptopLinkDetails->updated_by){
            $requestor = $recipient;
        }else{
            $requestor = Employees::where('id', $laptopLinkDetails->updated_by)->first();
        }

        if($laptopLinkDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            //reset the  data
            EmployeesLaptops::where('id', $id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                        'reasons' => $reason, 
                        'updated_by' => Auth::user()->id,
                        'approved_by' => Auth::user()->id,
                    ]);

            //create logs
            Logs::createLog("Laptop", 'Laptop Linkage Request Rejection');

            $mailData = [
                'link' => route('laptops.details', ['id' => $laptopLinkDetails->laptop_id]),
                'reason' => $reason,
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
                'requestor' => !empty($requestor) ? $requestor->first_name .' ' .$requestor->last_name : 'unknown',
                'assignee' => $recipient->first_name .' ' .$recipient->last_name,
                'tagNumber' => $laptopData->tag_number,
            ];

            Mail::to($recipient->email)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REJECTION')));

            $alert = 'Successfully rejected laptop linkage.';
        }else{
            //reset data
            $update['updated_by'] = Auth::user()->id;
            $update['approved_by'] = Auth::user()->id;
            $update['reasons'] = $reason;
            $update['update_data'] = NULL;
            $update['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            EmployeesLaptops::where('id', $id)
                    ->update($update);

            //create logs
            Logs::createLog("Laptop", 'Laptop Linkage Detail Update Rejection');

            //send mail to requestor
            $mailData = [
                'reason' => $reason,
                'firstName' => $recipient->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Laptop",
                'requestor' => !empty($requestor) ? $requestor->first_name .' ' .$requestor->last_name : 'unknown',
                'assignee' => $recipient->first_name .' ' .$recipient->last_name,
                'tagNumber' => $laptopData->tag_number,
            ];

            Mail::to($recipient->email)->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_LINKAGE_UPDATE_BY_NON_MANAGER_REJECTION')));
            
            $alert = 'Successfully rejected laptop linkage detail update.';    
        }

        session(['llr_alert'=> $alert]);
        return Redirect::back();
    }

    /**
     * additional validation for approval or rejection
     *
     * @param [type] $details
     * @return void
     */
    private function validateRequest($id, $type = self::LAPTOP_REQUEST){
        if(empty($id)){
            //id is not included in the request, show error page
            return 'Invalid request.';
        }

        $detail = $type == self::LAPTOP_REQUEST ? Laptops::where('id', $id)->first() : EmployeesLaptops::where('id', $id)->first();

        if(empty($detail)){
            if($type == self::LAPTOP_LINK_REQUEST){
                return 'Laptop linkage does not exists.';
            }else{
                return 'Laptop does not exists.';
            }
        }

        //check if employee needs to be approved
        if($detail->approved_status != config('constants.APPROVED_STATUS_PENDING')    //pending for new registration
            && $detail->approved_status != config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){    //pending for update
            if($type == self::LAPTOP_REQUEST){
                return 'Laptop has no pending request.';
            }else{
                return 'Laptop linkage has no pending request.';
            }
        }

        return ''; 
    }

    private function getDetailNote($details){
        $note = '';

        if($details['approved_status'] == config('constants.APPROVED_STATUS_PENDING')){
            $note = 'Registration is still pending';
        }elseif($details['approved_status'] == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
            $note = 'Update is still pending';
        }else{
            if(!$details['status']){
                $note = 'Laptop is inactive';
            }
        }       

        return $note;
    }
}
