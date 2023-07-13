<?php

namespace App\Http\Requests;

use App\Models\Projects;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProjectsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE');
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'end_date.after' => "The end date must be after the start date.",
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'start_date' => 'required|date',
            'remarks' => 'max:1024',
        ];
        if(strpos($this->header('referer'), route('projects.create')) !== FALSE){
            $rules['name'] = ['required', 'max:512', function ($attribute, $value, $fail) {
                $data = Projects::where('name', $value)->first();
                if (!empty($data)) {
                    $fail('The project has already been registered.');
                }
            }];
        }else{
            $id = $this->input('id');
            $rules['name'] = ['required', 'max:512', function ($attribute, $value, $fail) use ($id) {
                $data = Projects::where('name', $value)->where('id', '!=', $id)->first();
                if (!empty($data)) {
                    $fail('The project has already been registered.');
                }
            }];
        }

        if ($this->has('end_date') && $this->input('end_date') != '') {
            $rules['end_date'] = 'date|after:' .$this->input('start_date');
        }

        return $rules;
    }
}
