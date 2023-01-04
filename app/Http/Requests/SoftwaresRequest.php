<?php

namespace App\Http\Requests;

use App\Models\Softwares;
use App\Rules\AWSEmailAddress;
use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SoftwaresRequest extends FormRequest
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
            'software_name' => 'software name',
            'remarks' => 'remarks',
            'reasons' => 'reasons',
            'update_data' => 'update data',
            'type' => 'type'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    /*public function messages()
    {
        return [
            'birthdate.regex' => "The birth date must be a valid date.",
            'birthdate.date' => "The birth date must be a valid date.",
        ];
    }*/

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [];
        if($this->isMethod('POST')){
            $rules = [
                'software_name' => 'required|max:80',
                'type' => 'required|in:1,2,3,4,5,6',
                'remarks' => 'required|max:1024',
            ];
        }
        return $rules;
    }
}
