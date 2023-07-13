<?php

namespace App\Http\Requests;

use App\Models\Employees;
use App\Models\EmployeesProjects;
use App\Models\ProjectSoftwares;
use App\Models\Projects;
use App\Models\Softwares;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class LinkProject extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $referer = $this->header('referer');
        $slash_last_pos = strrpos($referer, '/');
        $id = substr($referer, $slash_last_pos + 1, strlen($referer) - $slash_last_pos - 1); 

        //check if the page is for employee
        if(strpos($this->header('referer'), route('projects.details', ['id' => $id])) !== FALSE && !Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'project_id' => 'project',
            'project_start' => 'start date',
            'project_end' => 'end date',
            'software_id' => 'software_name',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'project_start.after_or_equal' => "The start date must be on or after the project's start date.",
            'project_start.before' => "The start date must be before the project's end date.",
            'project_end.after' => "The end date must be after the project's start date.",
            'project_end.before_or_equal' => "The end date must be on or before the project's end date.",
        ];
    }
    /**
     * Returns error in json format
     *
     * @param Validator $validator
     * @return void
     */
    public function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ]));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {   
        $referer = $this->header('referer');
        //get the id this will be used for page checking
    
        $slash_last_pos = strrpos($referer, '/');
        $id = substr($referer,$slash_last_pos + 1, strlen($referer) - $slash_last_pos - 1); 

        $rules = array();
        $isLinkEmployee = $this->input('is_employee');
        $isUpdateLinkEmployee = $this->input('is_employee_update');
        $isFromProjectDetails = $this->input('is_from_ProjectDetails');

        // Validation for: employee linking to a project. 
        // From: employee OR project details page
        if(strpos($this->header('referer'), route('employees.details', ['id' => $id])) !== FALSE 
            || (strpos($this->header('referer'), route('projects.details', ['id' => $id])) !== FALSE && $isLinkEmployee) ){
            $employeeId = $this->input('employee_id');
            $projectDetails = Projects::where('id', $this->input('project_id'))->first();
            $emprules = [
                'employee_id' => ['required', 'exists:employees,id', function ($attribute, $value, $fail) {
                    // Checks if employee exists in DB
                    $employeeData = Employees::where('id', $value)
                                            ->where('active_status', 1)
                                            ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);

                    if (empty($employeeData)) {
                        $fail('The selected employee does not exist.');
                    }
                }],
                'project_id' => ['required', 'exists:projects,id', function($attribute, $value, $fail) use ($employeeId) {
                    if(EmployeesProjects::checkIfProjectIsOngoing($value, $employeeId)){
                        $fail('Employee is already a member of the selected project.');
                    }
                }],
    
                'project_role' => 'required|in:1,2,3',
                'remarks' => 'max:1024,'
            ];
    
            if(!empty($projectDetails)){
                $emprules['project_start'] = "required|date|after_or_equal:{$projectDetails->start_date}";
                if(!empty($projectDetails->end_date) && $projectDetails->end_date != "0000-00-00 00:00:00"){
                    $emprules['project_start'] = "required|date|after_or_equal:{$projectDetails->start_date}|before:{$projectDetails->end_date}";
                    if($this->filled('project_end')){
                        $emprules['project_end'] = "date|after:{$projectDetails->start_date}|before_or_equal:{$projectDetails->end_date}";
                    }
                }
            }

            $rules = $emprules;
        }
        // Validation for: Employee-Project linkage update
        else if (strpos($this->header('referer'), route('projects.details', ['id' => $id])) !== FALSE && $isUpdateLinkEmployee) {
            $projectDetails = Projects::where('id', $id)->first();

            $rules = [    
                'project_role' => 'required|in:1,2,3',
                'remarks' => 'max:1024,'
            ];

            if(!empty($projectDetails)){
                $rules['project_start'] = "required|date|after_or_equal:{$projectDetails->start_date}";
                if(!empty($projectDetails->end_date) && $projectDetails->end_date != "0000-00-00 00:00:00"){
                    $rules['project_start'] = "required|date|after_or_equal:{$projectDetails->start_date}|before:{$projectDetails->end_date}";
                    if($this->filled('project_end')){
                        $rules['project_end'] = "date|after:{$projectDetails->start_date}|before_or_equal:{$projectDetails->end_date}";
                    }
                }
            }
        }
        // Validation for: software linkage
        else if ((strpos($this->header('referer'), route('softwares.details', ['id' => $id])) !== FALSE) ||
                 (strpos($this->header('referer'), route('projects.details', ['id' => $id])) !== FALSE && $isFromProjectDetails)) {
            $softwareId = $this->input('software_id');
            $projectDetails = Projects::where('id', $this->input('project_id'))->first();
            $softrules = [
                'software_id' => ['required', 'exists:softwares,id', function ($attribute, $value, $fail) {
                    // Checks if software exists in DB
                    $softwareData = Softwares::where('id', $value)
                                            ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);
                    if (empty($softwareData)) {
                        $fail('The selected software does not exist.');
                    }
                }],
                'project_id' => ['required', 'exists:projects,id', function($attribute, $value, $fail) use ($softwareId) {
                    // Checks if project exists in DB
                    if(ProjectSoftwares::checkIfSoftwareExists($value, $softwareId)){
                        $fail('Selected project is already linked.');
                    }
                }],
                'remarks' => 'max:1024',
            ];
            $rules =  $softrules;
        }
        else {
            $softwareId = $this->input('software_id');
            $projectDetails = Projects::where('id', $this->input('project_id'))->first();
            $softrules = [
                'software_id' => ['required', 'exists:softwares,id', function ($attribute, $value, $fail) {
                    //check if software exists in DB
                    $softwareData = Softwares::where('id', $value)
                                            ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);

                    if (empty($softwareData)) {
                        $fail('The selected software does not exist.');
                    }
                }],
                'project_id' => ['required', 'exists:projects,id', function($attribute, $value, $fail) use ($softwareId) {
                    if(ProjectSoftwares::checkIfSoftwareExists($value, $softwareId)){
                        $fail('Selected Project name is already linked.');
                    }
                }],
                'remarks' => 'max:1024',
            ];
            $rules =  $softrules;
        }

        return $rules;
    }
}
