<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Mail\Software;
use App\Models\Projects;
use App\Models\Softwares;
use App\Models\SoftwareTypes;
use App\Models\Employees;
use Illuminate\Http\Request;
use App\Exports\SoftwaresExport;
use App\Models\ProjectSoftwares;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\SoftwaresRequest;
use Illuminate\Support\Facades\Redirect;

class SoftwaresController extends Controller
{
    
    /**
     * Display of software request
     *
     * @param string $rejectCode
     * @return void
     */
    public function create($rejectCode = ""){
        $software = '';
        $software_types = '';
        $newsoftwaretypes = null;

        //get approved software_types for display on the option 
        $software_types = SoftwareTypes::where('approved_status',  config('constants.APPROVED_STATUS_APPROVED'))
            ->get()->toArray();

        if($rejectCode){
 
            $software = Softwares::where('reject_code', $rejectCode)
            ->where('approved_status', config('constants.APPROVED_STATUS_REJECTED'))
            ->first();

            abort_if(empty($software), 404);

            if($software)
            {
                //new software type name and id dispay
                $newsoftwaretypes = SoftwareTypes::where([['id', $software->software_type_id],
                                                          ['approved_status',   config('constants.APPROVED_STATUS_PENDING')]])
                                                 ->first();

            }
        }

        return view('softwares.create')->with(['software' => $software,
                                                'software_types' => $software_types,
                                                'new_software_type' => $newsoftwaretypes]);
    }

    /**
     * Software registration process
     *
     * @param SoftwaresRequest $request
     * @return route to softwares.regist.complete
     */
    public function regist(SoftwaresRequest $request)
    {

        $request->validated();


        $insertData = $request->except("_token");
        $insertData['created_by'] = Auth::user()->id;
        $insertData['updated_by'] = Auth::user()->id;
        $id = null;
       

        if(empty($insertData['id'])){
            //new registration
            unset($insertData['id']);
            if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
                //approve the registration, no email is sent
                $insertData['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');
                $insertData['approved_by'] = Auth::user()->id;
                $insertData['approve_time'] = date('Y-m-d H:i:s');
            }else{
                //pending request, 
                $insertData['approved_status'] = config('constants.APPROVED_STATUS_PENDING');
            }

            //process software Type 
            $insertData = $this->processNewSoftwareType($insertData);
            //then uset "new_software_type"
            unset($insertData['new_software_type']);
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

            //process software Type 
            $insertData = $this->processNewSoftwareType($insertData);

            //then uset "new_software_type"
            unset($insertData['new_software_type']);
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
                'currentUserId' => Auth::user()->id,
                'module' => "Software",
            ];
            $this->sendMail($recipients, $mailData, config('constants.MAIL_SOFTWARE_NEW_REQUEST'));
        }
        
        return redirect(route('softwares.regist.complete'));
    }

     /**
     * Software Add / Update new software type data in the software_types table during creation of new software  
     * or during update of existing software
     * @param array $data
     * @return array $data
     */
    public function processNewSoftwareType($data)
    {
        //check the software_type. if software_type = others, 
        if($data['software_type_id'] == config('constants.SOFTWARE_TYPE_999'))
        {
            //check first if the current software type has a pending approval request
            $current_software_type = SoftwareTypes::where('type_name',$data['new_software_type'] )->first();
            if($current_software_type && $current_software_type->approved_status == config('constants.APPROVED_STATUS_PENDING') )
            {
                $software_type_id = $current_software_type->id;
            }
            else{
                //save first the new software type to software_types table
                
                $insertSoftwareTypeData["type_name"] = $data['new_software_type'];
                $insertSoftwareTypeData['created_by'] = Auth::user()->id;
                $insertSoftwareTypeData['updated_by'] = Auth::user()->id;
                if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
                    $insertSoftwareTypeData['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');
                }
                else{
                    $insertSoftwareTypeData['approved_status'] = config('constants.APPROVED_STATUS_PENDING');
                }

                //then get the new id of the new created software type and st it to the insert data
                $software_type_id = SoftwareTypes::create($insertSoftwareTypeData)->id;

                //create logs
                Logs::createLog("Software", 'Added Software Type id: ' . strval($software_type_id). ", type name: ".  $insertSoftwareTypeData["type_name"]);
            }

            //use the new software_type_id as the value ot softwares' software_type_id
            $data['software_type_id'] = $software_type_id; 
            
        }
        return $data;
    }

    /**
     * Software detail screen display
     *
     * @param [type] $id
     * @return view for softwares.details
     */ 
    public function detail($id){

        $softwareDetails = Softwares::getSoftwareDetail($id);
        $is_display_approver = true;
        $is_display_new_software_type = false;

        abort_if(empty($softwareDetails), 404); //software does not exist

        // Check if software is deleted
        if($softwareDetails->is_deleted == 1) {
            return view('error.requestError')->with([ 'error' => "This software is deleted" ]);
        }

        //check if new software type should be displayed
        if($softwareDetails->type_approved_status != config('constants.APPROVED_STATUS_APPROVED'))
        {
            $is_display_new_software_type = true;
        }

        //check if software has pending update,
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE') &&
            ($softwareDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')
            || (!$softwareDetails->active_status && $softwareDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')))){
            return redirect(route('softwares.request', ['id' => $id]));
        }

        //check if allowed to edit
        $allowedToEdit = false;
        if($softwareDetails->approved_status == config('constants.APPROVED_STATUS_APPROVED'))
        {
            $allowedToEdit = true;
        }
        $is_project_display = false;

        if($softwareDetails->approved_status == config('constants.APPROVED_STATUS_APPROVED') || 
        $softwareDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE') )
        {
            $is_project_display = true;
        }

        return view('softwares.details')
                    ->with([
                        'allowedToEdit' => $allowedToEdit,
                        'software' => $softwareDetails,
                        'softProject' => ProjectSoftwares::getProjectBySoftware($id),
                        'detailNote' => $this->getSoftwareStatus($softwareDetails),
                        'readOnly' => true,
                        'detailOnly' => true,
                        'current_status' => $this->transformStatusToText($softwareDetails),
                        'is_display_approver' => $is_display_approver,
                        'is_display_new_software_type' => $is_display_new_software_type,
                        'projectList' => Projects::getProjectDropdownPersoftware($id),
                        'is_project_display' => $is_project_display,
                    ]);
    }


    /**
     * Display of software edit screen
     *
     * @param [type] $id
     * @return void
     */ 
    public function edit($id){
        $software = Softwares::getSoftwareDetail($id);

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

        //get approved software_types for display on the option 
        $software_types = SoftwareTypes::where('approved_status',  config('constants.APPROVED_STATUS_APPROVED'))
        ->get()->toArray();

        if($software)
        {
            //new software type name and id for initial display if the software type id is not yet approved
            $newsoftwaretypes = SoftwareTypes::where([['id', $software->software_type_id],
                                                      ['approved_status',   config('constants.APPROVED_STATUS_PENDING')]])
                                             ->first();

        }
        
        return view('softwares.edit')->with([
                                        'software' => $software,
                                        'current_status' => $this->transformStatusToText($software),
                                        'software_types' => $software_types,
                                        'new_software_type' => $newsoftwaretypes
                                    ]);

    }

    /**
     * Software update process
     *
     * @param SoftwaresRequest $request
     * @return void
     */
    public function update(SoftwaresRequest $request){
        $request->validated();
        $updateData = $request->only(["id", "software_name", "software_type_id", "new_software_type", "remarks"]);
        
        $id = $updateData['id'];
        $originalData = Softwares::where('id', $id)->first();

        unset($updateData['id']);


        //process software Type 
        $updateData = $this->processNewSoftwareType($updateData);
        //then unset "new_software_type"
        unset($updateData['new_software_type']);

        //check logined employee role
        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')){
            //save directly in DB in db

            $updateData['updated_by'] = Auth::user()->id;

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
            //if an employee edit software and not the manager
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

    /**
     * Process for displaying the detail page once the "View" action is clicked from dashboard
     *
     * @param string $id
     * @return void
     */
    public function detailview($id)
    {

        if(Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE'))
        {
           return($this->request($id));
        }

        return redirect(route('softwares.details', ['id' => $id]));
    }

    /**
     * Process for deleting
     *
     * @param string $id
     * @return void
     */
    public function delete($id)
    {   
        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE'), 404);

        //save directly in DB in db

        $updateData['updated_by'] = Auth::user()->id;
        $updateData['is_deleted'] = 1;

        //unlink

        Softwares::where('id', $id)
            ->update($updateData);

        //unlink
        ProjectSoftwares::where('software_id', $id)
            ->update($updateData);


        //format log
        Logs::createLog("Software", "Software with ID: {$id} has been deleted by ".Auth::user()->first_name ." ". Auth::user()->last_name);

        return redirect(route('softwares.delete.complete'));
    }

    /**
     * Display softwares registration request or softwares update request
     *
     * @param [type] $id
     * @return void
     */
    public function request($id)
    {
        $softwaresDetails = Softwares::getSoftwareDetail($id);

        $is_display_approver = false;

        $is_display_new_software_type = false;        
        abort_if(empty($softwaresDetails), 404); //software does not exist

        $detailNote = $this->getSoftwareStatus($softwaresDetails);

        if($softwaresDetails->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            $detailNote = 'Software is still pending for approval';
        }elseif($softwaresDetails->approved_status == config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')){
            $detailNote = 'Software Update approval is still pending';
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

        //check if new software type should be displayed
        //get the status of the software type
        $software_type_status = SoftwareTypes::where('id', $softwaresDetails->software_type_id)->first();
        if($software_type_status->approved_status != config('constants.APPROVED_STATUS_APPROVED'))
        {
            $is_display_new_software_type = true;
            $softwaresDetails->type = $software_type_status->type_name;
        }
        else{
            //update the software type name based on the software_type_id retrieved from update_data 
            $softwaresDetails["type"] =  $software_type_status->type_name;
        }

        $is_project_display = false;
        return view('softwares.details')
        ->with([
            'allowedToEdit' => false,
            'readOnly' => true,
            'detailOnly' => false,
            'detailNote' => $detailNote,
            'showRejectCodeModal' => 1,
            'software' => $softwaresDetails,
            'current_status' => $this->transformStatusToText($softwaresDetails),
            'is_display_approver' => $is_display_approver,
            'is_display_new_software_type' => $is_display_new_software_type,            
            'is_project_display' => $is_project_display,
        ]);
    
    }

     /**
     * Process the approval of software request
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

        $softwares = Softwares::where('id',$id)->first();
        $employee =  Employees::where('id', $softwares->updated_by)->first();
        
        //if no error, process approval 
        if($softwares->approved_status == config('constants.APPROVED_STATUS_PENDING')){
            //approval of the new software_type
            //check first the status of the selected software_type
            $software_type = SoftwareTypes::where('id',$softwares->software_type_id)->first();
            if($software_type->approved_status == config('constants.APPROVED_STATUS_PENDING'))
            {
                //update the software_type's status
                SoftwareTypes::where('id',$softwares->software_type_id)
                    ->update([
                        'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                        'updated_by' => Auth::user()->id,
                    ]);
            }

            //if new registration
            Softwares::where('id', $softwares['id'])
                ->update([
                    'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                    'reasons' => NULL,
                    'approve_time' => date('Y-m-d H:i:s'),
                    'approved_by' => Auth::user()->id,
                        'prev_updated_by' => null, 
                ]);

            //send mail
            $mailData = [
                'first_name' => $employee->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Software",
                'link' => route('softwares.details', ['id' => $id])
            ];
            $this->sendMail($employee->email, $mailData, config('constants.MAIL_SOFTWARE_NEW_APPROVAL'));
                
            Logs::createLog("Software", "Approve software request for software {$softwares->id}");
        
        }else{



            $softwareUpdate = json_decode($softwares->update_data, true);
            $softwareUpdate['created_by'] = $softwares->created_by;
            $softwareUpdate['approved_by'] = Auth::user()->id;
            $softwareUpdate['approve_time'] = date('Y-m-d H:i:s');
            $softwareUpdate['update_data'] = NULL;
            $softwareUpdate['reasons'] = NULL;
            $softwareUpdate['approved_status'] = config('constants.APPROVED_STATUS_APPROVED');

            //update status of software type
            if(isset($softwareUpdate['software_type_id']))
            {
                $software_type = SoftwareTypes::where('id',$softwareUpdate['software_type_id'])->first();
                if($software_type->approved_status == config('constants.APPROVED_STATUS_PENDING'))
                {
                    //update the software_type's status
                    SoftwareTypes::where('id',$softwareUpdate['software_type_id'])
                        ->update([
                            'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                            'updated_by' => Auth::user()->id,
                        ]);
                }
            }
            //updated based on json 

            Softwares::where('id', $softwares['id'])->update($softwareUpdate);
            
            //logs
            $mailData = [
                'first_name' => $employee->first_name,
                'currentUserId' => Auth::user()->id,
                'module' => "Software",
                'link' => route('softwares.details', ['id' => $id])
            ];
            $this->sendMail($employee->email, $mailData, config('constants.MAIL_SOFTWARE_UPDATE_APPROVAL'));
                
            Logs::createLog("Software", "Approved Update of software {$softwares->id}");

        }

        return redirect(route('home'));
    }

    /**
     * Process the rejection of software requests
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
                    'approve_time' => date('Y-m-d H:i:s'),
                    'approved_by' => Auth::user()->id,  
                    'prev_updated_by' => $software->updated_by,                    
                ]);
            
            //send mail
            $mailData = [
                'first_name' => $employee->first_name,
                'reasons' => $reason,
                'link' => route('softwares.create') ."/{$rejectCode}",
                'currentUserId' => Auth::user()->id,
                'module' => "Software",
            ];
            
            $this->sendMail($employee->email, $mailData, config('constants.MAIL_SOFTWARE_NEW_REJECTION'));

            Logs::createLog("Software", "Reject software request with id {$software->id} for reason {$reason}.");
        }
        else{
            Softwares::where('id', $software['id'])
                ->update([
                    'approved_status' => config('constants.APPROVED_STATUS_APPROVED'),
                    'reasons' => $reason,
                    'update_data' => NULL,
                    'approve_time' => date('Y-m-d H:i:s'),
                    'approved_by' => Auth::user()->id,   
                    'prev_updated_by' => $software->updated_by,                  
                ]);
            
            //send mail
            $mailData = [
                'first_name' => $employee->first_name,
                'reasons' => $reason,
                'currentUserId' => Auth::user()->id,
                'link' => route('softwares.details', ['id' => $id]),
                'module' => "Software",
            ];
            $this->sendMail($employee->email, $mailData, config('constants.MAIL_SOFTWARE_UPDATE_REJECT'));
        
            //logs
            Logs::createLog("Software", "Reject software request with id {$software->id} for reason {$reason}.");
        }

        return redirect(route('home'));
    }

     
    /*
    * Clear rejected update
    */
    public function clearRejectedUpdate() {

        Softwares::where('prev_updated_by', Auth::user()->id)
                    ->where('approved_status', config('constants.APPROVED_STATUS_APPROVED'))
                    ->update([
                        'updated_by' => Auth::user()->id,
                        'prev_updated_by' => null,
                        'reasons' => null,
                    ]);
        //create logs
        Logs::createLog("Software", 'Rejected Software Update are all cleared.');
        return Redirect::back();
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
            return 'Software does not exist.';
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
     * @param Softwares $software
     * @return void
     */
    private function getSoftwareStatus($software){
        $note = '';

        switch ($software->approved_status){
            case config('constants.APPROVED_STATUS_APPROVED'):     //rejected registration
                $note = '';
                break;
            case config('constants.APPROVED_STATUS_REJECTED'):     //rejected registration
                $note = 'Software was rejected';
                break;
            case config('constants.APPROVED_STATUS_PENDING'):     //pending registration
                $note = 'Software is still pending for approval';
                break;
            case config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE'):
                $note = 'Software Update approval is still pending';
                break;
            default:    //invalid status 
                $note = 'Software detail is invalid.';
        }

        return $note;
    }

    /**
     * transforms the software approved_status into the equivalent Status text
     *
     * @param string $software
     * @return void
     */    
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

    /**
     * Removes the new line charactesd
     *
     * @param string $string
     * @return void
     */       
    private function removeNewLine(&$string){
        str_replace(["\n\r", "\n", "\r"], ' ', $string);
    }

    /**
     * Display software list
     *
     * @return void
     */    
    public function index(){
        $software_request = $this->getSoftware();
       
        $list_note_approve_on = "";
        $list_note_approve_by = "";

        $this->getLastSoftwareApproverNote($list_note_approve_by, $list_note_approve_on);
       
        //get the software list regardless of the approved status
        $software_types = SoftwareTypes::get()->toArray();

        return view('softwares/list', [
                                        'software_request' => $software_request,
                                        'list_note_approve_by' => $list_note_approve_by,
                                        'list_note_approve_on' => $list_note_approve_on,
                                        'software_types' => $software_types]);
    }

    /**
     * Get the latest approver and approve date for display
     * @param string &$list_note_approve_by
     * @param string &$list_note_approve_on
     * @return void
     */ 
    public function getLastSoftwareApproverNote(&$list_note_approve_by, &$list_note_approve_on)
    {
        $last_approved_software = Softwares::GetLastApproverDetail();

        if($last_approved_software)
        {
            if($last_approved_software->approver){
                $list_note_approve_by = 'Last approved by: ' . $last_approved_software->approver;
            }
            
            if($last_approved_software->approve_time)
            {
                $current_date = date("Y-m-d", strtotime($last_approved_software->approve_time) );
               
                $list_note_approve_on = 'Last approved on: ' . $current_date;
            }
            else 
            {
                $list_note_approve_on = 'Last approved on: <empty>' ;
            }
        }
    }

    /**
     * Get software data from softwares table
     * @return array $softwares
     */ 
    private function getSoftware() {
        $software = Softwares::getSoftwareForList();
        return $software;
    }

    
    /**
     * Download employee list
     *
     * @return void
     */
    public function download() {
        
        $current_date = date("Y-m");
        Logs::createLog("Software", "Downloaded list of software");
        // determine file type
        if (in_array(Auth::user()->roles, [config('constants.MANAGER_ROLE_VALUE'), config('constants.ADMIN_ROLE_VALUE')])) {

              return \Excel::download(new SoftwaresExport(), 'C4I DEV J Dev K SW Inventory (' . $current_date . ').xlsx', \Maatwebsite\Excel\Excel::XLSX);
        } else {
              return \Excel::download(new SoftwaresExport(), 'C4I DEV J Dev K SW Inventory (' . $current_date . ').pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

    }
}
