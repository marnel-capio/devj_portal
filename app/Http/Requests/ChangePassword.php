<?php

namespace App\Http\Requests;

use App\Rules\Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends FormRequest
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
     * Returns error in json json format
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
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'confirm_password.in' => "The password and confirm password don't match.",
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'current_password' => ['required',
            function($attribute, $value, $fail){
                if(!Hash::check($value, Auth::user()->password)){
                    $fail('The current password is incorrect.');
                }
            }
         ],
            'new_password' => ['required', 'min:8', 'max:16', new Password(), 'required_with:confirm_password'],
            'confirm_password' => ['required', 'min:8', 'max:16', 'in:'.$this->input('new_password')],
        ];
    }
}
