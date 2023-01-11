<?php

namespace App\Http\Controllers;

use Excel;
use App\Models\Logs;
use App\Mail\Software;
use App\Models\Projects;
use App\Models\Softwares;
use App\Models\Employees;
use Illuminate\Http\Request;
use App\Exports\SoftwaresExport;
use App\Models\ProjectSoftwares;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\SoftwaresRequest;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Log;

class SoftwaresController extends Controller
{
    
    public function create($rejectCode = ""){
        $software = '';
        if($rejectCode){
            $software = Softwares::where('reject_code', $rejectCode)
            ->where('approved_status', config('constants.APPROVED_STATUS_REJECTED'))
            ->first();

            abort_if(empty($software), 404);
        }

        return view('softwares.create')->with(['software' => $software]);
    }

    public function regist(SoftwaresRequest $request){

        $request->validated();


        $insertData = $request->input();
        $insertData['created_by'] = Auth::user()->id;
        $insertData['updated_by'] = Auth::user()->id;
        $id = null;
        //dd($insertData);


        //dd($insertData);

        if(empty($insertData['id'])){
            //new registration
            unset($insertData['id']);
            if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
                //approve the registration, no email is sent
                $insertData['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');
                $insertData['approved_by'] = Auth::user()->id;

                //$id = Softwares::create($insertData)->id;

            }else{
                //pending request, 
                $insertData['approved_status'] = config('constants.APPROVED_STATUS_PENDING');
                //$id = Softwares::create($insertData)->id;
            }
            $id = Softwares::create($insertData)->id;
        }        
        else{
            //registration update
            $id = $insertData['id'];
            unset($insertData['id']);
            $insertData['approved_status'] = config('constants.APPROVED_STATUS_PENDING');
            $insertData['reject_code'] = NULL;
            $insertData['reasons'] = NULL;
            $insertData['updated_by'] = Auth::user()->id;

            Softwares::where('id', $id)
                    ->update($insertData);
        }
        //create logs
        Logs::createLog("Software", 'Created Software Approval Request for software ' . strval($id) );

        //send mail if current user is not manager
        if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE')){
            //send mail to managers
            $recipients = Employees::getEmailOfManagers();

            $mailData = [
                'link' => route('softwares.request', ['id' => $id]),
                'currentUserId' => $id,
                'module' => "Software",
            ];
            $this->sendMail($recipients, $mailData, config('constants.MAIL_NEW_SOFTWARE_REQUEST'));
        }
        
        return redirect(route('softwares.regist.complete'));
    }

    public function detail($id){
        $softwareDetails = Softwares::where('id', $id)->first();
        $is_display_approver = true;

        $requestorDetail = Employees::where('id', $softwareDetails->updated_by)->first();
        $approverDetails = Employees::where('id', $softwareDetails->approved_by)->first();

        $requesterName = $requestorDetail->last_name . ", " . $requestorDetail->first_name . " " . $requestorDetail->middle_name; 
        $approverName = $approverDetails->last_name . ", " . $approverDetails->first_name . " " . $approverDetails->middle_name; 

        abort_if(empty($softwareDetails), 404); //software does not exist

        //check if software has pending update,
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE') &&
            ($softwareDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')
            || (!$softwareDetails->active_status && $softwareDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')))){
            return redirect(route('softwares.request', ['id' => $id]));
        }

        //check if allowed to edit
        $allowedToEdit = false;
        if((Auth::user()->id == $softwareDetails->updated_by )){
            $allowedToEdit = true;
        }

        return view('softwares.details')
                    ->with([
                        'allowedToEdit' => $allowedToEdit,
                        'software' => $softwareDetails,
                        'projectList' => ProjectSoftwares::getProjectBySoftware($id),
                        'detailNote' => $this->getSoftwareStatus($softwareDetails),
                        'readOnly' => true,
                        'detailOnly' => true,
                        'current_status' => $this->transformStatusToText($softwareDetails),
                        'is_display_approver' => $is_display_approver,
                        'requestor' => $requesterName,
                        'approver' => $approverName,
                    ]);
    }



    public function edit($id){
        $software = Softwares::where('id', $id)->first();
        $requestorDetail = Employees::where('id', $software->updated_by)->first();
        $approverDetails = Employees::where('id', $software->approved_by)->first();

        $requesterName = $requestorDetail->last_name . ", " . $requestorDetail->first_name . " " . $requestorDetail->middle_name; 
        $approverName = $approverDetails->last_name . ", " . $approverDetails->first_name . " " . $approverDetails->middle_name; 

        abort_if(empty($software), 404); //software does not exist

        //check if software has pending update
        if($software->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')
            || (!$software->active_status && $software->approved_status == config('constants.APPROVED_STATUS_PENDING'))){
            if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
                return redirect(route('software.request', ['id' => $id]));
            }else{
                abort(403);
            }
        }

        return view('softwares.edit')->with([
                                        'software' => $software,
                                        'requestor' => $requesterName,
                                        'approver' => $approverName,
                                        'current_status' => $this->transformStatusToText($software),
                                    ]);

    }

    public function update(SoftwaresRequest $request){
        $request->validated();
        $updateData = $request->except("_token");
        $id = $updateData['id'];
        $originalData = Softwares::where('id', $id)->first();
        //dd($updateData);

        unset($updateData['id']);
        unset($updateData['created_by']);

        //check logined employee role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            //save directly in DB in db
            unset($updateData['approved_status']);
            unset($updateData['approved_by']);
            Softwares::where('id', $id)
                ->update($updateData);

            //format log
            $log = "Software updated by manager: ";
            foreach($updateData as $key => $value){
                if($value != $originalData[$key] && !in_array($key, ['updated_by'])){
                    $log .= "{$key}: {$originalData[$key]} > {$value}, ";
                }
            }
            $log = rtrim($log, ", ");

            Logs::createLog("Software", $log);

            if(Auth::user()->id == $id){
                return redirect(route('softwares.details', ['id' => $id]))->with(['success' => 1, "message" => "Details are updated successfully."]);
            }else{
                return redirect(route('softwares.update.complete')); 
            }

        }else{
            //if an software edits his own data and is not the manager
            $json = [];
            foreach($updateData as $key => $value){
                if($value != $originalData[$key] && !in_array($key, ['updated_by'])){
                    $json[$key] = $value;
                }
            }
            Softwares::where('id', $id)
                        ->update([
                            'updated_by' => Auth::user()->id,
                            'update_data' => json_encode($json, true),
                            'approved_status' => config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')
                        ]);

            //notify the managers of the request
            $mailData = [
                'link' => route('softwares.request', ['id' => $id]),
                'requestor' => Auth::user()->first_name .' ' .Auth::user()->last_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Software",
            ];

            $this->sendMail(Employees::getEmailOfManagers(), $mailData, config('constants.MAIL_SOFTWARE_UPDATE_REQUEST'));
 
            Logs::createLog("Software", "Editted the software detail of {$id} Update_data: " .json_encode($json, true));
            return redirect(route('softwares.update.complete'));
        }
        
        
    }

    public function request($id){

        $softwaresDetails = Softwares::where('id', $id)->first();
        $requestorDetail = Employees::where('id', $softwaresDetails->updated_by)->first();
        $approverDetail = Employees::where('id', $softwaresDetails->approved_by)->first();
        

        $requesterName = $requestorDetail->last_name . ", " . $requestorDetail->first_name . " " . $requestorDetail->middle_name; 
        $approverName = $approverDetail->last_name . ", " . $approverDetail->first_name . " " . $approverDetail->middle_name; 

        $is_display_approver = false;

        //abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE'), 403);   //can only be accessed by manager

        abort_if(empty($softwaresDetails), 404); //software does not exist

        $detailNote = $this->getSoftwareStatus($softwaresDetails);
   
        if($softwaresDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            $detailNote = 'Software is still pending for approval';
        }elseif($softwaresDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
            $detailNote = 'Software Update is still pending';
        }

        //check if software has pending request

        if($softwaresDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
            //display software's update
            $updateData = json_decode($softwaresDetails->update_data, true);
            if(!empty($updateData)){
                foreach($updateData as $key => $val){
                    $softwaresDetails->$key = $val;
                }
            }
        }

        return view('softwares.details')
        ->with([
            'allowedToEdit' => false,
            'readOnly' => true,
            'detailOnly' => false,
            'detailNote' => $detailNote,
            'showRejectCodeModal' => 1,
            'software' => $softwaresDetails,
            'current_status' => $this->transformStatusToText($softwaresDetails),
            'requestor' => $requesterName,
            'approver' => $approverName,
            'is_display_approver' => $is_display_approver,
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

        $softwares = Softwares::where('id',$id)->first();
        $employee =  Employees::where('id', $softwares->updated_by)->first();
        
        //if no error, update employee details
        if($softwares->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            //if new registration
            Softwares::where('id', $softwares['id'])
                ->update([
                    'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                    'reasons' => NULL,
                    'updated_by' => Auth::user()->id,
                    'approved_by' => Auth::user()->id,
                ]);

            //send mail
            $this->sendMail($employee->email, ['first_name' => $employee->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Software",], config('constants.MAIL_NEW_SOFTWARE_APPROVAL'));
                
                Logs::createLog("Software", "Approve software request for software {$softwares->id}");
        
        }else{
            //update only
            $softwareUpdate = json_decode($softwares->update_data, true);
            $softwareUpdate['updated_by'] = Auth::user()->id;
            $softwareUpdate['approved_by'] = Auth::user()->id;
            $softwareUpdate['update_data'] = NULL;
            $softwareUpdate['reasons'] = NULL;
            $softwareUpdate['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            Softwares::where('id', $softwares['id'])->update($softwareUpdate);
            
            //logs
            $this->sendMail($employee->email, ['first_name' => $employee->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Software",], config('constants.MAIL_NEW_SOFTWARE_APPROVAL'));
                
            Logs::createLog("Software", "Approved Update of software {$softwares->id}");

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

        $software = Softwares::where('id',$id)->first();
        $employee = Employees::where('id', $software->updated_by)->first();
        $reason = $request->input('reason');
        $this->removeNewLine($reason);

        if($software->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            //if new registration
            $rejectCode = uniqid();
            Softwares::where('id', $software['id'])
                ->update([
                    'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                    'reasons' => $reason,
                    'reject_code' => $rejectCode,
                    'updated_by' => Auth::user()->id,
                ]);
            
            //send mail
            $mailData = [
                'first_name' => $employee->first_name,
                'reasons' => $reason,
                'link' => route('softwares.create') ."/{$rejectCode}",
                'currentUserId' => Auth::user()->id,
                'module' => "Software",
            ];
            $this->sendMail($employee->email, $mailData, config('constants.MAIL_NEW_SOFTWARE_REJECTION'));

            Logs::createLog("Software", "Reject software request with id {$software->id} for reason {$software->reasons}.");
        }
        else{
            Softwares::where('id', $software['id'])
                ->update([
                    'approved_status' => config('constants.APPROVED_STATUS_REJECTED'),
                    'reasons' => $reason,
                    'update_data' => NULL,
                    'updated_by' => Auth::user()->id,
                ]);
            
            //send mail
            $mailData = [
                'first_name' => $employee->first_name,
                'reasons' => $reason,
                'currentUserId' => Auth::user()->id,
                'module' => "Software",
            ];
            $this->sendMail($employee->email, $mailData, config('constants.MAIL_NEW_SOFTWARE_REJECTION'));
        
            //logs
            Logs::createLog("Software", "Reject software request with id {$software->id} for reason {$software->reasons}.");
        }

        return redirect(route('home'));
    }

    /**
     * Validates software's request before updating/rejecting
     *
     * @param [type] $id
     * @return void
     */
    private function validateRequest($id){
        if(empty($id)){
            //id is not included in the request, show error page
            return 'Invalid request.';
        }
        
        $software = Softwares::where('id', $id)->first();
        
        if(empty($software)){
            return 'Software does not exists.';
        }

        //check if software needs to be approved
        if(!($software->approved_status == config('constants.APPROVED_STATUS_PENDING'))    //pending for new registration
            && !$software->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){    //pending for update
                return 'Software has no pending request.';
            }

        return ''; 
    }


    /**
     * note on software's status in detail screen
     *
     * @param Software $software
     * @return void
     */
    private function getSoftwareStatus($software){
        $note = '';
        
        switch ($software->approved_status){
            case config('constants.APPROVED_STATUS_REJECTED'):     //rejected registration
                $note = 'Software was rejected';
                break;
            case config('constants.APPROVED_STATUS_PENDING'):     //pending registration
                $note = 'Software is still pending for approval';
                break;
            case config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'):
                $note = 'Software Update approval is still pending';
            default:    //account has been deactivated 
                $note = 'Softeware detail is invalid';
        }

        return $note;
    }

    private function transformStatusToText($software){
        $statut_text = '';
        switch ($software['approved_status']){
            case config('constants.APPROVED_STATUS_REJECTED'):     //rejected registration
                $statut_text = config('constants.APPROVED_STATUS_REJECTED_TEXT');
                break;
            case config('constants.APPROVED_STATUS_PENDING'):     //pending registration
                $statut_text = config('constants.APPROVED_STATUS_PENDING_TEXT');
                break;
            case config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'):
                $statut_text = config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE_TEXT');
                break;
            case config('constants.APPROVED_STATUS_APPROVED'):     //rejected registration
                $statut_text = config('constants.APPROVED_STATUS_APPROVED_TEXT');
                break;
           default:    //account has been deactivated 
                $statut_text = '';
        }

        return $statut_text;
    }


    /**
     * formats data for insert/update in softwares table
     *
     * @param SoftwaresRequest $request
     * @return array
     */
    /*Auth::check()){
            //for data update
            $data['created_by'] = Auth::user()->id;
            $data['updated_by'] = Auth::user()->id;
        }
        return $data;
    }
    */
    /**
     * send email
     *
     * @param array $recipients
     * @param array $mailData
     * @param int $mailType
     * @return void
     */
    private function sendMail($recipients, $mailData, $mailType){
        if (!empty($recipients)) {
            Mail::to($recipients)->send(new Software($mailData, $mailType));
        } 
        
    }

    private function removeNewLine(&$string){
        str_replace(["\n\r", "\n", "\r"], ' ', $string);
    }

    public function index(Request $request){
        $software_request = $this->getSoftware();

        return view('softwares/list', ['software_request' => $software_request]);
    }

    private function getSoftware() {
        $software = Softwares::whereIn('approved_status',[1,2,3,4])
                                ->orderBy('software_name', 'ASC')
                                ->get();

        return $software;
    }

    public function download(Request $request) {
        
        Logs::createLog("Software", "Downloaded list of software");
        // determine excel type
        if (Auth::user()->roles != 3) {
            return (new SoftwaresExport($request['searchInput'],$request['softwareStatus']))->download('C4I DEV J Dev K SW Inventory (2022-04).xlsx');
        } else {
            return (new SoftwaresExport($request['searchInput'],$request['softwareStatus'], 'pdf'))->download('C4I DEV J Dev K SW Inventory (2022-04).pdf');
        }

    }
}
