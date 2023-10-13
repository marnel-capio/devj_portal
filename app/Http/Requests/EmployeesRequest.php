<?php

namespace App\Http\Requests;

use App\Models\Employees;
use App\Rules\AWSEmailAddress;
use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
            'cellphone_number' => 'contact number',
            'current_address_postalcode' => 'postal code',
            'permanent_address_postalcode' => 'postal code',

            'passport_number' => 'Passport Number',
            'date_of_issue' => 'Date of Issue',
            'issuing_authority' => 'Issuing Authority',
            'passport_type' => 'Passport Type',
            'passport_expiration_date' => 'Passport Expiration Date',
            'place_of_issue' => 'Place of Issue',
            'date_of_appointment' => 'Date of Appointment',
            'no_appointment_reason' => 'Reason for No Appointment',
            'date_of_delivery' => 'Date of Delivery',
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
            'confirm_password.in' => "The password and confirm password don't match.",

            'birthdate.regex' => "The birth date must be a valid date.",
            'birthdate.date' => "The birth date must be a valid date.",
            'birthdate.before' => "The age must be at least 18 years old.",

            'permanent_address_province.in' => "The selected province is not valid",
            'current_address_province.in' => "The selected province is not valid",

            'current_address_postalcode.lt' => "The postal code exceeds max digits", 
            'permanent_address_postalcode.lt' => "The postal code exceeds max digits",
            
            // --- Passport --- //
            'date_of_issue.regex' => "The Date of Issue must be a valid date.",
            'date_of_issue.date' => "The Date of Issue must be a valid date.",
            'date_of_issue.before_or_equal' => "The Date of Issue must be on or before today.",
            
            'passport_expiration_date.regex' => "The Passport expiration date must be a valid date.",
            'passport_expiration_date.date' => "The Passport expiration date must be a valid date.",
            
            'date_of_appointment.regex' => "The Date of Appointment must be a valid date.",
            'date_of_appointment.date' => "The Date of Appointment date must be a valid date.",
            'date_of_appointment.after_or_equal' => "The Date of Appointment must be on or after today.",
            
            'date_of_delivery.regex' => "The Date of Delivery must be a valid date.",
            'date_of_delivery.date' => "The Date of Delivery date must be a valid date.",
            'date_of_delivery.after_or_equal' => "The Date of Delivery must be on or after today.",
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
                'last_name' => 'required|max:80|alpha_space',
                'birthdate' => 'required|date|regex:/^\d{4}-\d{2}-\d{2}$/|before:' . Carbon::now()->subYears(18)->format('Y-m-d'),
                'gender' => 'required|in:0,1',
                'email' => ['required', 'email', 'max:80', 'min:15', new AWSEmailAddress()],
                'cellphone_number' => 'required|numeric|digits:10',
                'current_address_street' => 'required|max:80',
                'current_address_postalcode' =>'required|numeric|gte:0|lt:100000000000',
                'permanent_address_street' => 'required|max:80',
                'permanent_address_postalcode' =>'required|numeric|gte:0|lt:100000000000',
            ];

            $selected_permanentProvince = $this->input('permanent_address_province');
            $selected_currentProvince = $this->input('current_address_province');

            // Validate if selected province exists in config file
            $rules['permanent_address_province'] = ['required', 'max:80', Rule::in(array_keys(config("provinces_cities.PROVINCES_CITIES")))];
            $rules['current_address_province'] = ['required', 'max:80', Rule::in(array_keys(config("provinces_cities.PROVINCES_CITIES")))];

            // If province exists in config file, validate if city is in selected province
            $rules['permanent_address_city'] = ['required', 'max:80', 
                                                (array_key_exists($selected_permanentProvince, (config("provinces_cities.PROVINCES_CITIES"))) ? 
                                                Rule::in(config("provinces_cities.PROVINCES_CITIES")[$selected_permanentProvince]) :
                                                 "in:'null'")];

            $rules['current_address_city'] = ['required', 'max:80', 
                                                (array_key_exists($selected_currentProvince, (config("provinces_cities.PROVINCES_CITIES"))) ? 
                                                Rule::in(config("provinces_cities.PROVINCES_CITIES")[$selected_currentProvince]) :
                                                 "in:'null'")];


            // Required based on `Positions` from config file
            $rules["position"] = ['required', Rule::in(array_keys(config('constants.POSITIONS')))];

            // Required based on `Passport status` from config file
            $passport_status = [];
            foreach(config('constants.PASSPORT_STATUS_LIST') as $status) {
                array_push($passport_status, $status["val"]);
            }
            $rules["passport_status"] = ['required', Rule::in($passport_status)];
            

            // If suffix field is not empty
            if(!empty($this->input('name_suffix'))) {
                $rules['name_suffix'] = 'max:80|alpha_space';
            }

            // If middle_name field is not empty
            if(!empty($this->input('middle_name'))) {
                $rules['middle_name'] = 'max:80|alpha_space';
            }

            switch($this->input('passport_status')) {
                case config('constants.PASSPORT_STATUS_WITH_PASSPORT_VALUE'): {
                    $rules = array_merge($rules, [
                        'passport_number' => 'required|max:80',
                        'issuing_authority' => 'required|max:80',
                        // 'passport_type' => 'required|in:1,2,3',
                        'place_of_issue' => 'max:80',
                        'date_of_issue' => 'required|date|regex:/^\d{4}-\d{2}-\d{2}$/|before_or_equal:today',
                        'passport_expiration_date' => 'required|date|regex:/^\d{4}-\d{2}-\d{2}$/|after:today',
                    ]);
                
                    $rules["passport_type"] = ['required', Rule::in(array_keys(config('constants.PASSPORT_TYPE')))];
                    break;
                }

                case config('constants.PASSPORT_STATUS_WITH_APPOINTMENT_VALUE'): {
                    $rules['date_of_appointment'] = 'required|date|regex:/^\d{4}-\d{2}-\d{2}$/|after_or_equal:today';
                    break;
                }
                    
                case config('constants.PASSPORT_STATUS_WAITING_FOR_DELIVERY_VALUE'): {
                    $rules['date_of_delivery'] = 'required|date|regex:/^\d{4}-\d{2}-\d{2}$/|after_or_equal:today';
                    break;

                }
                
                case config('constants.PASSPORT_STATUS_WITHOUT_PASSPORT_VALUE'): {
                    $rules['no_appointment_reason'] = 'required|max:1024';
                    break;
                }

                default:
                    break;
            }

            if(strpos($this->header('referer'), route('employees.create')) !== FALSE){
                $rules['password'] = ['required', 'min:8', 'max:16', new Password(), 'required_with:confirm_password'];
                $rules['confirm_password'] = ['required', 'min:8', 'max:16', 'in:'.$this->input('password')];
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
                
                $rules['active_status'] = [function($attribute, $value, $fail){
                    $employee = Employees::where('id', $this->input('id'))->first();
                    if($value != $employee->active_status && Auth::user()->roles != config ('constants.MANAGER_ROLE_VALUE')){
                        $fail('Active Status can only be updated by a Manager.');
                    }
                }];
                $rules['server_manage_flag'] = [function($attribute, $value, $fail){
                    $employee = Employees::where('id', $this->input('id'))->first();
                    if($value != $employee->server_manage_flag && Auth::user()->roles != config ('constants.MANAGER_ROLE_VALUE')){
                        $fail('Manage Server Flag can only be updated by a Manager.');
                    }
                }];
                $rules['is_admin'] = [function($attribute, $value, $fail){
                    $employee = Employees::where('id', $this->input('id'))->first();
                    $valueFromDB = $employee->roles == config('constants.ADMIN_ROLE_VALUE') ? 1 : 0;
                    if($value != $valueFromDB && Auth::user()->roles != config ('constants.MANAGER_ROLE_VALUE')){
                        $fail('Admin Flag can only be updated by a Manager.');
                    }
                }];
                $rules['id'] = [function($attribute, $value, $fail){
                    if(Auth::user()->id != $value  && !in_array(Auth::user()->roles, [config('constants.MANAGER_ROLE_VALUE'), config('constants.ADMIN_ROLE_VALUE')])){
                        $fail('An Employee with an Engineer role is not allowed to update the details of other employees.');
                    }
                }];
            }
        }
        return $rules;
    }
}
