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

    /**
     * Display laptop list screen
     *
     * @return void
     */
    public function index(){

        $laptops = Laptops::getLaptopList();

        return view('laptops.index')->with(['laptopList' => $laptops]);
    }

    /**
     * laptop list download process
     *
     * @param Request $request
     * @return void
     */
    public function download(Request $request){
        $data = $request->all();

        Logs::createLog("Laptop", "Downloaded employee's laptop details");
        // determine file type
        if (in_array(Auth::user()->roles, [config('constants.MANAGER_ROLE_VALUE'), config('constants.ADMIN_ROLE_VALUE')])) {
            return (new LaptopsExport($data['searchInput'], $data['laptopAvailability'], $data['laptopStatus'], $data['searchFilter']))->download('DevJ Laptop Details.xlsx');
        } else {
            return (new LaptopsExport($data['searchInput'], $data['laptopAvailability'], $data['laptopStatus'], $data['searchFilter'], true))->download('DevJ Laptop Details.pdf');
        }
    }

    /**
     * Display laptop registration screen
     *
     * @param string $rejectCode
     * @return void
     */
    public function create($rejectCode = ""){
        $laptop = '';
        $linkage = '';
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

            $linkage = EmployeesLaptops::where('laptop_id', $laptop->id)
                                            ->where('approved_status', config('constants.APPROVED_STATUS_PENDING'))
                                            ->first();
        }

        return view('laptops.create', ['laptop' => $laptop, 'linkage' => $linkage]);
    }

    /**
     * Laptop registration process
     *
     * @param LaptopsRequest $request
     * @return void
     */
    public function regist(LaptopsRequest $request){
        $request-> validated();
        
        $data = $request->except(['_token']);
        $data['status'] = 1;
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;

        if(Auth::user()->roles == config('constants.ENGINEER_ROLE_VALUE') || $data['linkage']['link_to_self']){
            //extract linkage data
            $linkageData = [
                'brought_home_flag' => $data['linkage']['brought_home_flag'] ? 1 : 0,
                'vpn_flag' => $data['linkage']['vpn_flag'] ? 1 : 0,
                'remarks' => $data['linkage']['remarks'],
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
                'employee_id' => Auth::user()->id,
            ];
        }
        //unset data for linkage
        unset($data['linkage']);

        if(empty($data['id'])){
            //new registration
            unset($data['id']);
            if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
                //approve the registration, no email is sent
                $data['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');
                $data['approved_by'] = Auth::user()->id;

                $id = Laptops::create($data)->id;

                if(isset($linkageData) && !empty($linkageData)){
                    $linkageData['laptop_id'] = $id;
                    $linkageData['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');
                    $linkageData['approved_by'] = Auth::user()->id;
                    EmployeesLaptops::create($linkageData);
                }

            }else{
                //pending request, 
                $data['approved_status'] = config('constants.APPROVED_STATUS_PENDING');
                $id = Laptops::create($data)->id;

                if(isset($linkageData) && !empty($linkageData)){
                    $linkageData['laptop_id'] = $id;
                    $linkageData['approved_status'] = config('constants.APPROVED_STATUS_PENDING');
                    EmployeesLaptops::create($linkageData);
                }
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

            //get data from employees_laptops data
            $origLinkageData = EmployeesLaptops::where('laptop_id', $id)
                                                    ->where('approved_status', config('constants.APPROVED_STATUS_PENDING'))
                                                    ->first();
            if(!empty($origLinkageData)){
                //check if data needs to be updated
                if(isset($linkageData) && !empty($linkageData)){
                    //update linkage data
                    EmployeesLaptops::where('id', $origLinkageData->id)->update($linkageData);
                }else{
                    //reject original linkage
                    EmployeesLaptops::where('id', $origLinkageData->id)
                                        ->update([
                                            'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                                            'updated_by' => Auth::user()->id,
                                            'prev_updated_by' => $origLinkageData->updated_by,
                                        ]);
                }
            }else{
                if(isset($linkageData) && !empty($linkageData)){
                    //create new
                    $linkageData['laptop_id'] = $id;
                    EmployeesLaptops::create($linkageData);
                }
            }
            
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

    /**
     * Display Laptop detail screen
     *
     * @param [type] $id
     * @return void
     */
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


    /**
     * Display laptop requests
     *
     * @param [type] $id
     * @return void
     */
    public function request($id){
        $laptopDetails = Laptops::where('id', $id)
            ->whereIn('approved_status', [config('constants.APPROVED_STATUS_PENDING'),config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
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

        $linkage = EmployeesLaptops::where('laptop_id', $id)
                    ->where('approved_status', config('constants.APPROVED_STATUS_PENDING'))
                    ->first();

        return view('laptops.details')->with([
            'detail' => $laptopDetails,
            'requestor' => $requestor,
            'detailNote' => $detailNote,
            'detailOnly' => false,
            'linkage' => $linkage,
        ]);
    }

    /**
     * Laptop request approval process
     *
     * @param Request $request
     * @return void
     */
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
                        'prev_updated_by' => null,
                    ]);
            
            //check if pending registration has linkage data
            $linkageData = EmployeesLaptops::where('laptop_id', $id)
                                            ->where('approved_status', config('constants.APPROVED_STATUS_PENDING'))
                                            ->first();
            if(!empty($linkageData)){
                //approve the linkage
                EmployeesLaptops::where('id', $linkageData->id)
                                    ->update([
                                        'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                                        'approved_by' => Auth::user()->id,
                                        'updated_by' => Auth::user()->id,
                                        'prev_updated_by' => null,
                                    ]);
            }

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
            $update['prev_updated_by'] = NULL;
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

    /**
     * Laptop request rejection process
     *
     * @param Request $request
     * @return void
     */
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
                        'prev_updated_by' => $laptopDetails->updated_by,
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
                        'prev_updated_by' => $laptopDetails->updated_by,
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

    /**
     * Employee-Laptop Linkage request approval process
     *
     * @param Request $request
     * @return void
     */
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
                        'prev_updated_by' => null,
                        'reasons' => null,
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
            $update['prev_updated_by'] = NULL;
            $update['reasons'] = NULL;
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

    /**
     * Rejects other employee-laptop linkagre request when a laptop has been assigned to an employee
     *
     * @param [type] $laptop_id
     * @return void
     */
    private function rejectOtherLinkageRequest($laptop_id){
        $pendingApproval = EmployeesLaptops::getLinkRequestByLaptop($laptop_id, config('constants.APPROVED_STATUS_PENDING'));
        if(!empty($pendingApproval)){
            $reason = 'Laptop has been assigned to other employee';
            $pendingIds = array_column($pendingApproval, 'id');
    
    
            //send mail
            foreach($pendingApproval as $request => $data){
                EmployeesLaptops::where('id', $data['id'])
                                ->update([
                                    'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                                    'approved_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id,
                                    'reasons' => $reason,
                                    'prev_updated_by' => $data['employee_id'],
                                ]);
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
                Logs::createLog('Laptop', 'Laptop Linkage Request Rejection');
                Mail::to($data['email'])->send(new MailLaptops($mailData, config('constants.MAIL_LAPTOP_NEW_LINKAGE_BY_NON_MANAGER_REJECTION')));
            }
        }
    }

    /**
     * Employee-Laptop Linkage request rejection process
     *
     * @param Request $request
     * @return void
     */
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
                        'prev_updated_by' => $laptopLinkDetails->updated_by,
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
            $update['prev_updated_by'] = $laptopLinkDetails->employee_id;
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


    /*
    * Clear rejected linkage
    */
    public function clearRejectedLinkage() {

        EmployeesLaptops::where('prev_updated_by', Auth::user()->id)
                    ->update([
                        'updated_by' => Auth::user()->id,
                        'prev_updated_by' => null,
                    ]);
        //create logs
        Logs::createLog("Laptop", 'Rejected Laptop Linkage are all cleared.');
        return Redirect::back();    
    }

    
    /*
    * Clear rejected update
    */
    public function clearRejectedUpdate() {

        Laptops::where('prev_updated_by', Auth::user()->id)
                    ->where('approved_status', config('constants.APPROVED_STATUS_APPROVED'))
                    ->update([
                        'updated_by' => Auth::user()->id,
                        'prev_updated_by' => null,
                        'reasons' => null,
                    ]);
        //create logs
        Logs::createLog("Laptop", 'Rejected Laptop Update are all cleared.');
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

    /**
     * Returns a shor note of the laptop's status
     *
     * @param [type] $details
     * @return void
     */
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
