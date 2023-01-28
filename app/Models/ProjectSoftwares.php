<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSoftwares extends Model
{
    use HasFactory;

    protected $table = 'projects_softwares';
    protected $guarded = [];
    
    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';

    static function getProjectBySoftware($id){
        
        return self::selectRaw('projects_softwares.id, 
                                projects_softwares.approved_by,
                                projects_softwares.approved_status,
                                projects_softwares.software_id,
                                projects_softwares.reasons,
                                projects_softwares.delete_flag,
                                projects_softwares.created_by,
                                projects_softwares.updated_by,
                                projects_softwares.create_time,
                                projects.name as name,
                                projects.id as project_id,
                                projects.start_date as start_date,
                                projects.end_date as end_date,
                                projects_softwares.update_time,
                                case when isnull(projects.end_date) 
                                    then "Ongoing" 
                                    else "Ended"
                                    end as project_status',
                                )
                    ->leftJoin('projects', 'projects.id',  'projects_softwares.project_id')
                    ->where('projects_softwares.software_id', $id)
                    ->orderByRaw('name')
                    ->get()
                    ->toArray();
                    
    }
}
