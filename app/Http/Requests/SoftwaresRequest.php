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
            'remarks' => 'purpose',
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
                'software_name' => ['required', 'max:80'],
                'type' => 'required|in:1,2,3,4,5,6',
                'remarks' => 'required|max:1024',
            ];

            $referer = $this->header('referer');
            $rejectCode = substr($referer, strripos($referer, '/') + 1);
 

            if(strpos($this->header('referer'), route('softwares.create')) !== FALSE){
                if(!empty($this->input('id'))){

                    $rules['software_name'] = ['required',
                                               'max:80',
                                                function($attribute, $value, $fail) use ($rejectCode){
                                                    $detail = Softwares::where('software_name', $value)->get()->toArray();
                                                    if(!empty($detail) && $detail[0]['id'] != $rejectCode){
                                                        $fail("The software is already registered.");
                                                    }
                                                }
                                            ];
                }else{
                    $rules['software_name'] = ['required',
                                               'max:80',
                                                function($attribute, $value, $fail){
                                                    $duplicatesoftware = Softwares::where('software_name', $value)->get()->toArray();
                                                    if(!empty($duplicatesoftware)){
                                                        $fail("The software is already registered.");
                                                    }
                    }];
                }
            }
            else{
                //get the id from the referer
                $slash_last_pos = strrpos($referer, '/');
                $next_to_last = 0;
                if($slash_last_pos != 0)
                {
                    $next_to_last = strrpos($referer, '/', $slash_last_pos - strlen($referer) - 1); 
                    $software_id = substr($referer,$next_to_last + 1, $slash_last_pos - $next_to_last - 1); //minus 1 for buffer
                    if(strpos($this->header('referer'), route('softwares.edit', ['id' => $software_id])) !== FALSE){
                        $rules['software_name'] = ['required',
                        'max:80',
                        function($attribute, $value, $fail) use ($software_id){
                            $detail = Softwares::where('software_name', $value)->get()->toArray();
                            if(!empty($detail) && $detail[0]['id'] != $software_id){
                                $fail("The software is already registered.");
                            }
                         }
                     ];
                    }                    
                }
            }
        }
        return $rules;
    }
}
