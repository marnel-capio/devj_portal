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
                                projects_softwares.requested_by,
                                projects_softwares.approved_by,
                                projects_softwares.approved_status,
                                projects_softwares.project_id,
                                projects_softwares.software_id,
                                projects_softwares.reasons,
                                projects_softwares.delete_flag,
                                projects_softwares.created_by,
                                projects_softwares.updated_by,
                                projects_softwares.create_time,
                                projects_softwares.update_time',
                                )
                    ->where('projects_softwares.software_id', $id)
                    ->get()
                    ->toArray();
                    
    }
}
