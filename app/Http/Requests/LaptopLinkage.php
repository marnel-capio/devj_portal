<?php

namespace App\Http\Requests;

use App\Models\EmployeesLaptops;
use App\Models\Laptops;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class LaptopLinkage extends FormRequest
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
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'remarks.max' => "The remarks must not be greater than 1024 characters.",
            'surrender_date.required_if' => "The surrender date is required if the surrender flag is checked.",
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
        $rules = [];
        if(strpos(route('api.updateLaptopLinkage'), $this->path()) !== FALSE){
            $id = $this->input('id');
            $rules = [
                'surrender_date' => 'required_if:surrender_flag,1',
                'remarks' => 'max:1024'
            ];
    
            if($this->has('surrender_date') && $this->input('surrender_flag')){
                $rules['surrender_date'] = ["required_if:surrender_flag,1", "date", function($attribute, $value, $fail) use ($id){
                    $data = EmployeesLaptops::where('id', $id)->first();
                    if($value < $data->create_date){
                        $fail('The surrender date must be greater than the borrow date.');
                    }
                }];
            }
        }elseif(strpos(route('api.registLaptopLinkage'), $this->path()) !== FALSE){
            $rules = [
                'assignee' => ["required", "exists:employees,id", function($attribute, $value, $fail){
                    if(Auth::user()->roles == config('constants.ENGINEER_ROLE_VALUE') && $value != Auth::user()->id){
                        $fail('An employee with engineer role is not allowed to link a laptop to other employee.');
                    }
                }],
                'remarks' => 'max:1024',
                'id' => [
                        function($attribute, $value, $fail){
                            $data = Laptops::where('id', $value)->first();
                            if((!empty($data) && !$data->status)){
                                $fail('Invalid laptop.');
                            }
                            if(empty(Laptops::getLaptopEmployeeDetails($value))){
                                $fail('Invalid laptop.');
                            }
                        }],
            ];
        }

        return $rules;

    }
}
