<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Mail\passwordResetMail;
use App\Models\Employees;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{

    /**
     * Login display and execution
     *
     * @return void
     */
    public function index(LoginRequest $request){
        if($request->isMethod('GET')){
            return view('login.login');
        }
        $request->validated();
        $credentials = [
            'email' => $request->input('email_address'),
            'password' => $request->input('password'),
        ];
        
        if(!Auth::validate($credentials)){
            $request->flash();
            return Redirect::back()->withErrors(['password' => 'The username and password does not match.']);            
        }
        $user = Auth::getProvider()->retrieveByCredentials($credentials);

        Auth::login($user);
        return redirect()->intended();
    }


    /**
     * Get the reject code and email address
     *
     * @param LoginRequest $request
     * @return page Error if  invalid data
     */
    public function rejectedRegistration(LoginRequest $request){
        if($request->isMethod('GET')){
            return view('login.rejectedRegistration');
        }
        $request->validated();

        // Get user details
        $email = $request->input('email_address');
        $reject_code = $request->input('reject_code');
        $employee = Employees::where('email', $email)
                                ->where('approved_status', config('constants.APPROVED_STATUS_REJECTED'))
                                ->where('reject_code', $reject_code)
                                ->first();

        if(empty($employee)) {
            return view('error.requestError')
            ->with([ 'error' => "Email and Reject code does not match" ]);
        } else {
            return redirect(route('employees.create', ['rejectCode' => $reject_code]));

        }
    }


    /**
     * Password reset
     *
     * @param LoginRequest $request
     * @return void
     */
    public function forgotPassword(LoginRequest $request){
        if($request->isMethod('GET')){
            return view('login.forgotPassword');
        }
        $request->validated();

        //get user details
        $email = $request->input('email_address');
        $employee = Employees::where('email', $email)
                                ->where('active_status', 1)
                                ->first()
                                ->toArray();

        //generate new Password
        $newPassword = $this->generatePassword();

        //update password in DB
        Employees::where('id', $employee['id'])
        ->update(['password' => password_hash($newPassword, PASSWORD_BCRYPT)]);

        //send mail
        $mailData = [
            'email' => $email,
            'first_name' => $employee['first_name'],
            'password' => $newPassword,
            'module' => 'Login',
            'currentUserId' => $employee['id'],
        ];
        Mail::to($email)->send(new passwordResetMail($mailData));
        $request->flash();
        return Redirect::back()->with('successMsg', 'Your new password has been sent to your email.');
    }

    /**
     * Generate temporary password
     *
     * @return string
     */
    private function generatePassword(){
        
        $length = 8; //password length
        $sets = [];
        $sets[] = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $sets[] = str_split('abcdefghijklmnopqrstuvwxyz');
        $sets[] = str_split('0123456789');
        $sets[] = str_split('!@#$%&*_');
        $password = '';
        
        //get 1 character from each set
        foreach($sets as $set){
            $password .= $set[array_rand($set)];
        }

        while(strlen($password) < $length){
            $randomSet = $sets[array_rand($sets)];
            $password .= $randomSet[array_rand($randomSet)];
        }

        return str_shuffle($password);
    }
}
