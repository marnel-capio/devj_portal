<?php

namespace App\Http\Requests;

use App\Models\Laptops;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LinkLaptop extends FormRequest
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
            'laptop_id' => 'laptop',
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
            'surrender_date.required_if' => "The surrender date is required when the surrender flag is checked.",
        ];
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $validation = [
            'laptop_id' => ['required', function($attribute, $value, $fail){
                if(empty(Laptops::getLaptopEmployeeDetails($value))){
                    $fail('The selected laptop in invalid, please select again.');
                }
            }],
            'surrender_date' =>'required_if:surrender_flag,1',
        ];
        
        if($this->filled('surrender_date')){
            $validation['surrender_date'] = 'date';
        }
        return  $validation;
    }
}
