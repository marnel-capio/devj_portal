<?php

namespace App\Http\Requests;

use App\Models\Laptops;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LaptopsRequest extends FormRequest
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
            'peza_form_number' => 'PEZA form number',
            'peza_permit_number' => 'PEZA permit number',
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
            'tag_number.unique' => "The tag number is already registered.",
            'remarks.max_digits' => "The remarks must not be greater than 1024 characters.",
            'tag_number.unique' => "The tag number is already registered.",
        ];
    }

    /**
     * Returns error in json format
     * For laptop detail update
     *
     * @param Validator $validator
     * @return void
     */
    public function failedValidation(Validator $validator){
        if($this->has('isUpdate') && $this->input('isUpdate')){
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors()
            ]));
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {   
        $rules = [
            'peza_form_number' => 'required',
            'peza_permit_number' => 'required',
            'laptop_make' => 'required',
            'laptop_model' => 'required',
            'laptop_cpu' => 'required',
            'laptop_clock_speed' => 'required',
            'laptop_ram' => 'required',
            'remarks' => 'max_digits:1024',
        ];

        if(strpos($this->header('referer'), route('laptops.create')) !== FALSE){

            $rules['tag_number'] = 'required|unique:laptops,tag_number';

            if(!empty($this->input('id'))){
                $referer = $this->header('referer');
                $rejectCode = substr($referer, strripos($referer, '/') + 1);
                $rules['tag_number'] = ['required', 
                    function($attribute, $value, $fail) use ($rejectCode){
                        $detail = Laptops::where('tag_number', $value)->get()->toArray();
                        if(!empty($detail) && $detail[0]['reject_code'] != $rejectCode){
                            $fail("The tag number is already registered.");
                        }
                    }
                ];
                
                $rules['id'] = [
                    function($attribute, $value, $fail) use ($rejectCode){
                        $detail = Laptops::where('id', $value)->get()->toArray();
                        if(!empty($detail) && $detail[0]['reject_code'] != $rejectCode){
                            $fail("Reject code does not match the laptop id.");
                        }
                    }
                ];
            }
        }

        return $rules;
    }
}
