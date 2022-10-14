<?php

namespace App\Http\Requests;

use App\Rules\AWSEmailAddress;
use App\Rules\AccountStatus;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{

    // protected $redirect = route('login');
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
        $customAttributes = [];
        if(strpos($this->header('referer'), route('login')) !== FALSE){
            $customAttributes['email_address'] = 'username';
        }
        return $customAttributes;
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
            $rules = ['email_address' => ['required', 'email', 'max:80', 'min:15', new AWSEmailAddress(), new AccountStatus()]];
            if(strpos($this->header('referer'), route('login')) !== FALSE){
                $rules['password'] = 'required|max:80|min:8';
            }
        }
        return $rules;
    }
}
