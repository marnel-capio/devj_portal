<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePassword;
use App\Http\Requests\LinkLaptop;
use App\Http\Requests\LinkProject;
use App\Models\Employees;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function changePassword(ChangePassword $request){
        
        $request->validated();
        $data = $request->only(['id', 'new_password']);

        //save new password
        Employees::where('id', $data['id'])
        ->update(['password' => password_hash($data['new_password'], PASSWORD_BCRYPT)]);

        return response()->json(['success' => true], 200);
    }

    public function linkLaptop(LinkLaptop $request){
        $request->validated();






        return response()->json(['success' => true], 200);
    }

    public function linkProject(LinkProject $request){
        $request->validated();





        
        return response()->json(['success' => true], 200);
    }

}
