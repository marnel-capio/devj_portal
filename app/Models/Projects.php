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

    static function getProjectDropdownPerEmployee($id){

        return self::select('id', 'name', )
                ->whereNotIn('id', function($query) use ($id){
                                        $query->select('project_id')
                                                ->from('employees_projects')
                                                ->where('employee_id', $id)
                                                ->whereNull('end_date');
                                    })
                ->get()
                ->toArray();
                
    }
}
