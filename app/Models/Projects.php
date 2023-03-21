<?php

namespace App\Models;

use App\Mail\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Projects extends Model
{
    use HasFactory;

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';
    protected $guarded = [];

    static function getProjectDropdownPerEmployee($id){

        return self::select('id', 'name', 'start_date', 'end_date')
                ->whereNotIn('id', function($query) use ($id){
                                        $query->select('project_id')
                                                ->from('employees_projects')
                                                ->where('employee_id', $id)
                                                ->whereRaw('(end_date IS NULL or end_date > CURDATE())');

                                    })
                ->get()
                ->toArray();
                
    }

    static function getProjectDropdownPersoftware($id){
        $project_list = self::select('id', 'name', 'start_date', 'end_date')
                ->whereNotIn('id', function($query) use ($id){
                                        $query->select('project_id')
                                                ->from('projects_softwares')
                                                ->where('software_id', $id);
                                                
                                    })
                ->get()
                ->toArray();

        return $project_list;
                
    }    
}
