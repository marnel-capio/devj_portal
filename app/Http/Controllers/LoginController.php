<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Mail\passwordResetMail;
use App\Models\Employees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

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
        $credentials = [];
        $credentials['email'] = $request->input('email_address');
        $credentials['passsword'] = $request->input('password');
        
        // $request->only(['email_address', 'password']);
        
        // dd( $credentials);
        if(Auth::attempt($credentials)){
            return redirect('/');
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
            'password' => $newPassword
        ];
        Mail::to($email)->send(new passwordResetMail($mailData));
        dd('mail sent');
    }

    
    private function generatePassword(){
        
        $length = 8; //password length
        $sets = [];
        $sets[] = str_split('ABCDEFGHJKLMNPQRSTUVWXYZ');
        $sets[] = str_split('abcdefghjkmnpqrstuvwxyz');
        $sets[] = str_split('23456789');
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
