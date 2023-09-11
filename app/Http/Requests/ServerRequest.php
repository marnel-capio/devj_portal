<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ServerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE') && !Auth::user()->server_manage_flag) {
            return false;
        }
        return true;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {   
        $attributes = [
            'memory_used' => 'used size',
            'memory_used_unit' => 'unit for used size',
            'memory_used_percentage' => 'percentage of used size',
            'memory_free' => 'free size',
            'memory_free_unit' => 'unit for free',
            'memory_free_percentage' => 'percentage of free size',
            'memory_total' => 'total size',
            'memory_total_unit' => 'unit for total size',
            'other_os_percentage' => 'CPU usage percentage',
            'function_role' => 'function or role',
            'server_ip' => 'IP address',
            'os' => 'OS',
            'cpu' => 'processor',
            'server_hdd' => 'HDD',
            'us' => '%us',
            'ni' => '%ni',
            'sy' => '%sy',
        ];

        //hdd partitions
        for ( $i = 1 ; $i <= $this->input('partitions_count') ; $i++ ) {
            $attributes = array_merge($attributes, [
                'hdd.' .$i .'.partition_name' => 'partition name',
                'hdd.' .$i .'.used' => 'used size',
                'hdd.' .$i .'.used_unit' => 'unit for used size',
                'hdd.' .$i .'.used_percentage' => 'percentage of used size',
                'hdd.' .$i .'.free' => 'free size',
                'hdd.' .$i .'.free_unit' => 'unit for free size',
                'hdd.' .$i .'.free_percentage' => 'percentage of free size',
                'hdd.' .$i .'.total' => 'total size',
                'hdd.' .$i .'.total_unit' => 'unit for total size',
            ]);
        }

        return $attributes;
    }

    /**
     * Sets the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [
            'hdd.min' => 'The server cannot be registered without an HDD partition.',

            // Validations below prevents app break for max input, but this validation does not exist in BD as of 2023-08-31
            'memory_used.lt' => "'Used size' number of digits exceeded max value.",
            'memory_free.lt' => "'Free size' number of digits exceeded max value.",
            'memory_total.lt' => "'Total size' number of digits exceeded max value.",
        ];


        // Validations below prevents app break for max input, but this validation does not exist in BD as of 2023-08-31
        // HDD partitions
        for ( $i = 1 ; $i <= $this->input('partitions_count') ; $i++ ) {
            $messages = array_merge($messages, [
                'hdd.' .$i .'.used.lt' => "'Used size' number of digits exceeded max value.",
                'hdd.' .$i .'.free.lt' => "'Free size' number of digits exceeded max value.",
                'hdd.' .$i .'.total.lt' => "'Total size' number of digits exceeded max value.",
            ]);
        }


        return $messages;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            //server details
            'server_name' => 'required|max:80',
            'server_ip' => 'required|max:80',
            'os' => 'required|max:80',
            'motherboard' => 'max:80',
            'cpu' => 'required|max:80',
            'memory' => 'required|max:80',
            'server_hdd' => 'required|max:80',
            'function_role' => 'required|max:1024',
            'os_type' => 'required|in:1,2',
            'remarks' => 'max:1024',
            //memory usage details
            'memory_used' => 'required|decimal:0,2|gte:0|lt:1000000',
            'memory_used_unit' => 'required|in:1,2,3,4,5',
            'memory_used_percentage' => 'required|decimal:0,2|gte:0|lte:100',
            'memory_free' => 'required|decimal:0,2|gte:0|lt:1000000',
            'memory_free_unit' => 'required|in:1,2,3,4,5',
            'memory_free_percentage' => 'required|decimal:0,2|gte:0|lte:100',
            'memory_total' => 'required|decimal:0,2|gt:0|lt:1000000',
            'memory_total_unit' => 'required|in:1,2,3,4,5',
            'hdd' => 'min:1',
        ];

        //cpu usage
        if ($this->input('os_type') == 1) {
            $rules = array_merge($rules, [
                'us' => 'required|decimal:0,2|gte:0|lte:100',
                'ni' => 'required|decimal:0,2|gte:0|lte:100',
                'sy' => 'required|decimal:0,2|gte:0|lte:100',
            ]);
        } else {
            $rules['other_os_percentage'] = 'required|decimal:0,2|gte:0|lte:100';
        }

        //hdd_usage
        for ( $i = 1 ; $i <= $this->input('partitions_count') ; $i++ ) {
            $rules = array_merge($rules, [
                'hdd.' .$i .'.partition_name' => 'required|max:80',
                'hdd.' .$i .'.used' => 'required|decimal:0,2|gte:0|lt:1000000',
                'hdd.' .$i .'.used_unit' => 'required|in:1,2,3,4,5',
                'hdd.' .$i .'.used_percentage' => 'required|decimal:0,2|gte:0|lte:100',
                'hdd.' .$i .'.free' => 'required|decimal:0,2|gte:0|lt:1000000',
                'hdd.' .$i .'.free_unit' => 'required|in:1,2,3,4,5',
                'hdd.' .$i .'.free_percentage' => 'required|decimal:0,2|gte:0|lte:100',
                'hdd.' .$i .'.total' => 'required|decimal:0,2|gt:0|lt:1000000',
                'hdd.' .$i .'.total_unit' => 'required|in:1,2,3,4,5',
            ]);
        }

        return $rules;
    }
}
