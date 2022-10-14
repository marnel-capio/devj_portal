<?php

namespace App\Rules;

use App\Models\Employees;
use Illuminate\Contracts\Validation\Rule;

class AccountStatus implements Rule
{
    private $message;
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
        $employee = Employees::where('email', $value)
                                ->first();
            if(!$employee['active_status']){
                switch ($employee['approved_status']){
                    case 3:
                        $this->message = 'Your account is still pending for approval';
                        break;
                    case 1:
                        $this->message = 'Your account has been rejected. Please check your email for more information.';
                        break;
                    default:
                    dd("hello");

                        $this->message = 'The account doesnot exists.';
                }

            }else{
                return true;
            }
        return !empty($employee);
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
