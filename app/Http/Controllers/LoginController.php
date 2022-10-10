<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\Employees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{

    /**
     * Displays the login page
     *
     * @return void
     */
    public function index(){

    //    dd("login page"); 
        return view('login.login');
    }

    /**
     * login function
     *
     * @param LoginRequest $request
     * @return void
     */
    public function execute(LoginRequest $request){



    }

    public function forgotPassword(){
        dd("forgot password");
        // return view('login.forgotPassword');

    }

    public function resetPassword(ResetPasswordRequest $request){

    }

    private function updatePassword(){
        //updates password in DB
    }

    
    private function generatePassword(){
        //generate password
    }
}
