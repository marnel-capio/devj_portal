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
                                projects_softwares.software_id,
                                projects_softwares.remarks,
                                projects_softwares.created_by,
                                projects_softwares.updated_by,
                                projects_softwares.create_time,
                                projects.name as name,
                                projects.id as project_id,
                                projects.start_date as start_date,
                                projects.end_date as end_date,
                                projects_softwares.update_time,
                                case when isnull(projects.end_date) 
                                    then "' . config("constants.PROJECT_STATUS_ONGOING_TEXT") . '" 
                                    else "' . config("constants.PROJECT_STATUS_FINISH_TEXT") . '"
                                    end as project_status',
                                )
                    ->leftJoin('projects', 'projects.id',  'projects_softwares.project_id')
                    ->where('projects_softwares.software_id', $id)
                    ->orderByRaw('name')
                    ->get()
                    ->toArray();
                    
    }

    static function checkIfSoftwareExists($projectId, $softwareID){
        $detail = self::where('project_id', $projectId)
                        ->where('software_id', $softwareID)
                            ->get()
                            ->toArray();
        return !empty($detail);
    }    

    /**
     * Return the linked softwares by project id
     *
     * @param [type] $id
     * @return void
     */
    static function getLinkedSoftwareByProject ($id) {
        return self::selectRaw('
                    s.software_name,
                    st.type_name as software_type,
                    s.remarks,
                    ps.remarks as linkageRemarks,
                    ps.id,
                    ps.software_id
                ')
                ->from('projects_softwares AS ps')
                ->leftJoin('softwares AS s', 's.id', 'ps.software_id')
                ->leftJoin('software_types AS st', 'st.id', 's.software_type_id')
                ->where('ps.project_id', $id)
                ->orderBy('s.software_name', 'asc')
                ->orderBy('ps.id', 'asc')
                ->get()
                ->toArray();
    }
}
