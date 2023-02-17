<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Servers;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    /**
     * Display the servers
     *
     * @return void
     */
    public function index(){

        return view('servers.index', [
            'serverData' => Servers::getAllServer(),
        ]);
    }

    public function download(){
        dd('Pending download function');
    }

    public function details($id){
        dd('welcome to server detail page of ' .$id);
    
    }

    public function create(){
        return view('servers.create', [
            'serverData' => []
        ]);
    }

    public function regist(Request $request){
        dd($request->except(['_token']));
    }
}
