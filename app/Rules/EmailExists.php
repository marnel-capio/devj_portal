<?php

namespace App\Rules;

use App\Models\Employees;
use Illuminate\Contracts\Validation\Rule;

class EmailExists implements Rule
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
        $employee = Employees::where('email', $value)
                                ->where('active_status', 1)
                                ->first();

        return !empty($employee);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The email address is not registered.';
    }
}
