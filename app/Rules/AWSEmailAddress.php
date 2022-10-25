<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AWSEmailAddress implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {   
        $offset = strpos($value, '@') === FALSE ? -11 : strpos($value, '@') + 1;
        return substr($value, $offset) === 'awsys-i.com';
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be your AWS email address.';
    }
}
