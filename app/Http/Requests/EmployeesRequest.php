<?php

namespace App\Http\Requests;

use App\Models\Employees;
use App\Rules\AWSEmailAddress;
use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class EmployeesRequest extends FormRequest
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
            'current_address_street' => 'street',
            'current_address_town' => 'town or city',
            'current_address_province' => 'province or region',
            'current_address_postal_code' => 'postal code',
            'permanent_address_street' => 'street',
            'permanent_address_town' => 'town or city',
            'permanent_address_province' => 'province or region',
            'permanent_address_postal_code' => 'postal code',
            'email' => 'email address',
        ];
    }

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
                'first_name' => 'required|max:80|alpha_space',
                'middle_name' => 'required|max:80|alpha_space',
                'last_name' => 'required|max:80|alpha_space',
                'birthdate' => 'required|date',
                'gender' => 'required|in:0,1',
                'position' => 'required|in:1,2,3,4,5,6,7,8,9',
                'email' => ['required', 'email', 'max:80', 'min:15', new AWSEmailAddress()],
                'cellphone_number' => 'required|numeric|digits:10',
                'password' => ['required', 'min:8', 'max:16', new Password()],
                'current_address_street' => 'required',
                'current_address_city' => 'required',
                'current_address_province' => 'required',
                'current_address_postalcode' =>'required|numeric',
                'permanent_address_street' => 'required',
                'permanent_address_city' => 'required',
                'permanent_address_province' => 'required',
                'permanent_address_postalcode' =>'required|numeric',
            ];

            if(!empty($this->input('other_contact_number'))){
                $rules['other_contact_number'] = 'numeric|digits:10';
            }

            if(strpos($this->header('referer'), route('employees.regist')) !== FALSE){
                $rules['email'] = ['required', 
                                    'email', 
                                    'max:80', 
                                    'min:15', 
                                    new AWSEmailAddress(), 
                                    function($attribute, $value, $fail){
                                        $duplicateEmail = Employees::where('email', $value);
                                        if(!empty($duplicateEmail)){
                                            $fail("The email address is already registered.");
                                        }
                                    }];
            }
        }
        return $rules;
    }
}
