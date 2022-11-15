<?php

namespace App\Http\Requests;

use App\Models\EmployeesProjects;
use App\Models\Projects;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LinkProject extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
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
            'project_end' => 'end date'
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
            'project_end.before_or_equal' => "The end date must be on or before the project's end date."
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
        $employeeId = $this->input('employee_id');
        $projectDetails = Projects::where('id', $this->input('project_id'))->first();
        $rules = [
            'project_id' =>['required', 'exists:projects,id', function($atribute, $value, $fail) use ($employeeId) {
                if(EmployeesProjects::checkIfProjectIsOngoing($value, $employeeId)){
                    $fail('Employee is already a member of the selected project.');
                }
            }],

            'project_role' => 'required|in:1,2,3'
        ];

        if(!empty($projectDetails) && !empty($projectDetails->end_date) && $projectDetails->end_date != "0000-00-00 00:00:00"){
            $rules['project_start'] = "required|date|after_or_equal:{$projectDetails->start_date}|before:{$projectDetails->end_date}";
            if($this->filled('project_end')){
                $rules['project_end'] = "required|date|after:{$projectDetails->start_date}|before_or_equal:{$projectDetails->end_date}";
            }
        }

        return $rules;
    }
}
