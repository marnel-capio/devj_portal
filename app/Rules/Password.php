<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Password implements Rule
{
    private $message;


    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

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
        //contains atleast 1 upper case letter
        if(!preg_match('@[A-Z]@', $value)){
            $this->message = "The password must contain at least 1 upper case letter.";
            return false;
        }

        //contains atleast 1 lower case letter
        if(!preg_match('@[a-z]@', $value)){
            $this->message = "The password must contain at least 1 lower case letter.";
            return false;
        }

        //contains atleast 1 numeric character
        if(!preg_match('@[0-9]@', $value)){
            $this->message = "The password must contain at least 1 number.";
            return false;
        }

        //contains atleast 1 numeric character
        if(!preg_match('@[!\@#\$%&*_]@', $value)){
            $this->message = "The password must contain at least 1 of the following special characters: !@#$%&*_.";
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
