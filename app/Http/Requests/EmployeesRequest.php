<?php

namespace App\Http\Requests;

use App\Models\Employees;
use App\Rules\AWSEmailAddress;
use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'current_address_city' => 'town or city',
            'current_address_province' => 'province or region',
            'current_address_postal_code' => 'postal code',
            'permanent_address_street' => 'street',
            'permanent_address_city' => 'town or city',
            'permanent_address_province' => 'province or region',
            'permanent_address_postal_code' => 'postal code',
            'email' => 'email address',
            'roles' => 'role',
            'cellphone_number' => 'contact number',
            'current_address_postalcode' => 'postal code',
            'permanent_address_postalcode' => 'postal code',
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

            if(strpos($this->header('referer'), route('employees.create')) !== FALSE){
                $rules['password'] = ['required', 'min:8', 'max:16', new Password()];
                $referer = $this->header('referer');
                $rejectCode = substr($referer, strripos($referer, '/') + 1);
                if(!empty($this->input('id'))){
                    $rules['email'] = ['required', 
                                        'email', 
                                        'max:80', 
                                        'min:15', 
                                        new AWSEmailAddress(), 
                                        function($attribute, $value, $fail) use ($rejectCode){
                                            $detail = Employees::where('email', $value)->get()->toArray();
                                            if(!empty($detail) && $detail[0]['reject_code'] != $rejectCode){
                                                $fail("The email address is already registered.");
                                            }
                                        }
                                    ];
                }else{
                    $rules['email'] = ['required',
                    'email', 
                    'max:80', 
                    'min:15', 
                    new AWSEmailAddress(), 
                    function($attribute, $value, $fail){
                        $duplicateEmail = Employees::where('email', $value)->get()->toArray();
                        if(!empty($duplicateEmail)){
                            $fail("The email address is already registered.");
                        }
                    }];
                }
                
            }

            if(strpos($this->header('referer'), '/edit') !== FALSE){
                $rules['roles'] = [function($attribute, $value, $fail){
                    $employee = Employees::where('id', $this->input('id'))->first();
                    if($value != $employee->roles && !in_array(Auth::user()->roles, [config('constants.MANAGER_ROLE_VALUE'), config('constants.ADMIN_ROLE_VALUE')])){
                        $fail('Role can only be update by an Admin or a Manager.');
                    }
                }];
                if(in_array(Auth::user()->roles, [config('constants.MANAGER_ROLE_VALUE'), config('constants.ADMIN_ROLE_VALUE')])){
                    $rules['roles'] = 'required|in:1,2,3';
                }
                $rules['active_status'] = [function($attribute, $value, $fail){
                    $employee = Employees::where('id', $this->input('id'))->first();
                    if($value != $employee->active_status && !in_array(Auth::user()->roles, [config('constants.MANAGER_ROLE_VALUE'), config('constants.ADMIN_ROLE_VALUE')])){
                        $fail('Active Status can only be update by an Admin or a Manager.');
                    }
                }];
                $rules['server_manage_flag'] = [function($attribute, $value, $fail){
                    $employee = Employees::where('id', $this->input('id'))->first();
                    if($value != $employee->server_manage_flag && !in_array(Auth::user()->roles, [config('constants.MANAGER_ROLE_VALUE'), config('constants.ADMIN_ROLE_VALUE')])){
                        $fail('Manage Server Flag can only be update by an Admin or a Manager.');
                    }
                }];
                $rules['id'] = [function($attribute, $value, $fail){
                    if(Auth::user()->id != $value  && !in_array(Auth::user()->roles, [config('constants.MANAGER_ROLE_VALUE'), config('constants.ADMIN_ROLE_VALUE')])){
                        $fail('An Employee with an Engineer role can not update the details of other employees.');
                    }
                }];
            }
        }
        return $rules;
    }
}
