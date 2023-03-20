<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectsRequest;
use App\Models\Logs;
use App\Models\Projects;
use Illuminate\Support\Facades\Auth;

class ProjectsController extends Controller
{
    public function index () {
        dd('project list coming soon');
    }
    
    public function create () {

        abort_if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE'), 403);
        return view('projects.create');
    }

    public function regist (ProjectsRequest $request) {
        $request->validated();

        //save data in DB
        $insertData = $request->except('_token');
        $insertData['created_by'] = Auth::user()->id;
        $insertData['updated_by'] = Auth::user()->id;
        Projects::create($insertData);

        //create logs
        Logs::createLog('Projects', 'Project registration of ' .$insertData['name'] .'.');

        //add success message to session
        session(['regist_update_alert' => 'Project was successfully registered!']);

        return redirect('projects.details');
    }

    public function detail ($id) {

        //get project data
        $projectData = Projects::where('id', $id)->first();


        return view('projects.details', [
            'projectData' => $projectData,
            'isManager' => Auth::user()->roles == config('constants.MANAGER_ROLE_VALUE'),
            'detailNote' => '', 
        ]);
    }

    public function edit ($id) {
        dd('edit page');
    }

}
