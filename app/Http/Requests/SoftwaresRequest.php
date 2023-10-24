<?php

namespace App\Http\Requests;

use App\Models\Softwares;
use App\Models\SoftwareTypes;
use Illuminate\Foundation\Http\FormRequest;


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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [];
        $software_type =$this->input('software_type_id');
        if($this->isMethod('POST')){
            $rules = [
                'software_name' => ['required', 'max:80'],
                'software_type_id' => ['required', 
                                        function($attribute, $value, $fail) {
                                            $detail = SoftwareTypes::where('id', $value)
                                                                    ->where('approved_status', config('constants.APPROVED_STATUS_APPROVED'))
                                                                    ->get()
                                                                    ->toArray();
                                            if(empty($detail) && $value != config('constants.SOFTWARE_TYPE_999'))
                                            {
                                                $fail("The selected type is invalid.");
                                            }
                                        }                                  
                                      ], 
               'remarks' => 'required|max:1024',
               'new_software_type' => ['max:80', 
                                        // checking if field is required or not
                                        function($attribute, $value, $fail) use ($software_type) {
                                            //if selected software type is others and new software name is empty
                                            if($software_type == config('constants.SOFTWARE_TYPE_999') && ($value == "" || $value == null))
                                            {
                                                $fail("The new software type field is required.");
                                            }
                                        }, 
                                        //checking if entered value is already existing in the approved software type
                                        function($attribute, $value, $fail) use ($software_type) {
                                            //get current software type list
                                            if($software_type == config('constants.SOFTWARE_TYPE_999'))
                                            {
                                                $detail = SoftwareTypes::where([['type_name', $value], 
                                                                                ['approved_status', config('constants.APPROVED_STATUS_APPROVED')]])->first();

                                                if($detail)
                                                {
                                                    $fail("Inputted software type is already existing.");
                                                }
                                            }
                                        }                                        
                                      ]
            ];

            $referer = $this->header('referer');
            $rejectCode = substr($referer, strripos($referer, '/') + 1);
 

            if(strpos($this->header('referer'), route('softwares.create')) !== FALSE){
                if(!empty($this->input('id'))){
                   
                    $rules['software_name'] = ['required',
                                               'max:80',
                                                function($attribute, $value, $fail) use ($rejectCode){
                                                    $detail = Softwares::where('software_name', $value)->where('is_deleted',"!=",1)->where('approved_status',"!=",config("constants.CANCEL_REGIST"))->get()->toArray();
                                                    if(!empty($detail) && $detail[0]['reject_code'] != $rejectCode){
                                                        $fail("The software is already registered.");
                                                    }
                                                }
                                            ];
                }else{
                    $rules['software_name'] = ['required',
                                               'max:80',
                                                function($attribute, $value, $fail){
                                                    $duplicatesoftware = Softwares::where('software_name', $value)->where('is_deleted',"!=",1)->where('approved_status',"!=",config("constants.CANCEL_REGIST"))->get()->toArray();
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
                            $detail = Softwares::where('software_name', $value)->where('is_deleted',"!=",1)->where('approved_status',"!=",config("constants.CANCEL_REGIST"))->get()->toArray();
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
