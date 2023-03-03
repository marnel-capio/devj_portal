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
        if (Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE') && !Auth::user()->server_admin_flag) {
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
            'memory_used' => 'used',
            'memory_used_unit' => 'unit for used size',
            'memory_used_percentage' => 'percentage of used size',
            'memory_free' => 'free memory',
            'memory_free_unit' => 'unit for free',
            'memory_free_percentage' => 'percentage of free size',
            'memory_total' => 'total',
            'memory_total_unit' => 'unit for total size',
            'other_os_percentage' => 'CPU usage',
            'function_role' => 'function or role',
            'server_ip' => 'ip address',
            'os' => 'OS',
            'cpu' => 'processor',
            'server_hdd' => 'HDD',
        ];

        //hdd partitions
        for ( $i = 1 ; $i <= $this->input('partitions_count') ; $i++ ) {
            $attributes = array_merge($attributes, [
                'hdd.' .$i .'.partition_name' => 'partition name',
                'hdd.' .$i .'.used' => 'used',
                'hdd.' .$i .'.used_unit' => 'unit for used size',
                'hdd.' .$i .'.used_percentage' => 'percentage of used size',
                'hdd.' .$i .'.free' => 'free',
                'hdd.' .$i .'.free_unit' => 'unit for free size',
                'hdd.' .$i .'.free_percentage' => 'percentage of free size',
                'hdd.' .$i .'.total' => 'total',
                'hdd.' .$i .'.total_unit' => 'unit for total size',
            ]);
        }

        return $attributes;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'hdd.min' => 'The server cannot be registered without an HDD partition.'
        ];
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
            'os_type' => 'in:1,2',
            'remarks' => 'max:1024',
            //memory usage details
            'memory_used' => 'required|decimal',
            'memory_used_unit' => 'required|in:1,2,3,4,5',
            'memory_used_percentage' => 'required|decimal',
            'memory_free' => 'required|decimal',
            'memory_free_unit' => 'required|in:1,2,3,4,5',
            'memory_free_percentage' => 'required|decimal',
            'memory_total' => 'required|decimal',
            'memory_total_unit' => 'required|in:1,2,3,4,5',
            'hdd' => 'min:1',
        ];

        //cpu usage
        if ($this->input('os_type') == 1) {
            $rules = array_merge($rules, [
                'us' => 'required|decimal',
                'ni' => 'required|decimal',
                'sy' => 'required|decimal',
            ]);
        } else {
            $rules['other_os_percentage'] = 'required|decimal';
        }

        //hdd_usage
        for ( $i = 1 ; $i <= $this->input('partitions_count') ; $i++ ) {
            $rules = array_merge($rules, [
                'hdd.' .$i .'.partition_name' => 'required|max:80',
                'hdd.' .$i .'.used' => 'required|decimal',
                'hdd.' .$i .'.used_unit' => 'required|in:1,2,3,4,5',
                'hdd.' .$i .'.used_percentage' => 'required|decimal',
                'hdd.' .$i .'.free' => 'required|decimal',
                'hdd.' .$i .'.free_unit' => 'required|in:1,2,3,4,5',
                'hdd.' .$i .'.free_percentage' => 'required|decimal',
                'hdd.' .$i .'.total' => 'required|decimal',
                'hdd.' .$i .'.total_unit' => 'required|in:1,2,3,4,5',
            ]);
        }

        return $rules;
    }
}
