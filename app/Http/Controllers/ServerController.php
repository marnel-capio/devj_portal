<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Servers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServerController extends Controller
{
    /**
     * Display the servers
     *
     * @return void
     */
    public function index(){
        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE') && !Auth::user()->server_admin_flag, 403);

        return view('servers.index', [
            'serverData' => Servers::getAllServer(),
        ]);
    }

    public function download(){
        dd('Pending download function');
    }

    public function details($id){
        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE') && !Auth::user()->server_admin_flag, 403);

        dd('welcome to server detail page of ' .$id);
    
    }

    public function create(){
        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE') && !Auth::user()->server_admin_flag, 403);

        return view('servers.create', [
            'serverData' => []
        ]);
    }

    public function regist(Request $request){
        dd($request->except(['_token']));
    }
}
