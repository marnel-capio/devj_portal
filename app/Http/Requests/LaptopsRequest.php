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
            'linkage.remarks' => 'remarks',
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
            'peza_form_number.unique' => "The PEZA form number is already registered.",
            'peza_permit_number.unique' => "The PEZA permit number is already registered.",
            'remarks.max' => "The remarks must not be greater than 1024 characters.",
            'linkage[remarks].max' => "The remarks must not be greater than 1024 characters.",
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
            'peza_form_number' => 'max:80',
            'peza_permit_number' => 'max:80',
            'laptop_make' => 'max:80|required',
            'laptop_model' => 'max:80|required',
            'laptop_cpu' => 'max:80|required',
            'laptop_clock_speed' => 'required|numeric|min:0|max:99.99',
            'laptop_ram' => 'required|integer|min:0|max:255',
            'remarks' => 'max:1024',
            'linkage.remarks' => 'max:1024',
        ];

        // 1. If request is from Create Laptop
        if(strpos($this->header('referer'), route('laptops.create')) !== FALSE){

            $rules['tag_number'] = 'required|max:80|unique:laptops,tag_number';
            if(empty($this->input('peza_form_number')) and empty($this->input('peza_permit_number'))){
                $rules['peza_form_number'] = 'max:80';
                $rules['peza_permit_number'] = 'max:80';
            } else {
                $rules['peza_form_number'] = 'max:80|required_with:peza_permit_number|unique:laptops,peza_form_number';
                $rules['peza_permit_number'] = 'max:80|required_with:peza_form_number|unique:laptops,peza_permit_number';
            }

            // 1.1 If request is from viewing a rejected laptop registration
            if(!empty($this->input('id'))){
                $referer = $this->header('referer');
                $rejectCode = substr($referer, strripos($referer, '/') + 1);
                $rules['tag_number'] = ['required','max:80', 
                    function($attribute, $value, $fail) use ($rejectCode){
                        $detail = Laptops::where('tag_number', $value)->get()->toArray();
                        if(!empty($detail) && $detail[0]['reject_code'] != $rejectCode){
                            $fail("The tag number is already registered.");
                        }
                    }
                ];
                
                if(!empty($this->input('peza_form_number')) or !empty($this->input('peza_permit_number'))){
                    $rules['peza_form_number'] = ['max:80',
                                                    function($attribute, $value, $fail) use ($rejectCode){
                                                        $detail = Laptops::where('peza_form_number', $value)->get()->toArray();
                                                        if(!empty($detail) && $detail[0]['reject_code'] != $rejectCode){
                                                            $fail("The PEZA form number is already registered.");
                                                        }
                                                    }, 
                                                    'required_with:peza_permit_number'
                    ];
                    $rules['peza_permit_number'] = ['max:80',
                                                    function($attribute, $value, $fail) use ($rejectCode){
                                                        $detail = Laptops::where('peza_permit_number', $value)->get()->toArray();
                                                        if(!empty($detail) && $detail[0]['reject_code'] != $rejectCode){
                                                            $fail("The PEZA permit number is already registered.");
                                                        }
                                                    }, 
                                                    'required_with:peza_form_number'
                    ];
                }
                
                $rules['id'] = [
                    function($attribute, $value, $fail) use ($rejectCode){
                        $detail = Laptops::where('id', $value)->get()->toArray();
                        if(!empty($detail) && $detail[0]['reject_code'] != $rejectCode){
                            $fail("Reject code does not match the laptop id.");
                        }
                    }
                ];
            }
        } else if(strpos($this->header('referer'), "/laptops") !== FALSE){
        // 2. If request is not from Laptop Create (e.g. Laptop Details>Update Laptop)
            $referer = $this->header('referer');
            $laptop_id = substr($referer, strripos($referer, '/') + 1);

            $rules['tag_number'] = [
                'required',
                'max:80', 
                function($attribute, $value, $fail) use ($laptop_id){
                    // Test if tag_number is unique from laptop table, and not self
                    $detail = Laptops::where('tag_number', $value)->where('id','<>',$laptop_id)->get()->toArray();
                    if(!empty($detail)){
                        $fail($this->messages()['tag_number.unique']);
                    }
                }
                
            ];
            if(empty($this->input('peza_form_number')) and empty($this->input('peza_permit_number'))){
                $rules['peza_form_number'] = 'max:80';
                $rules['peza_permit_number'] = 'max:80';
            } else {
                $rules['peza_form_number'] = [
                    'max:80',
                    'required_with:peza_permit_number',
                    function($attribute, $value, $fail) use ($laptop_id){
                        // Test if peza_form_number is unique from laptop table, and not self
                        $detail = Laptops::where('peza_form_number', $value)->where('id','<>',$laptop_id)->get()->toArray();
                        if(!empty($detail)){
                            $fail($this->messages()['peza_form_number.unique']);
                        }
                    }];
                $rules['peza_permit_number'] = [
                    'max:80',
                    'required_with:peza_form_number',
                    function($attribute, $value, $fail) use ($laptop_id){
                        // Test if peza_permit_number is unique from laptop table, and not self
                        $detail = Laptops::where('peza_permit_number', $value)->where('id','<>',$laptop_id)->get()->toArray();
                        if(!empty($detail)){
                            $fail($this->messages()['peza_permit_number.unique']);
                        }
                    }];
            }
        }

        return $rules;
    }
}
