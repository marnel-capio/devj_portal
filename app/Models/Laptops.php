<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Laptops extends Model
{
    use HasFactory;

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';
    protected $guarded = [];

    const LAPTOP_SEARCH_FILTER = [
        1 => 'tag_number',
        2 => 'laptop_make',
        3 => 'laptop_model',
        4 => 'laptop_cpu',
        5 => 'laptop_clock_speed',
        6 => 'laptop_ram',
    ];

    static function getLaptopDropdown($employeeId){

        return self::select('id', 'tag_number', )
                ->where('status', 1)
                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                ->whereNotIn('id', function($query) use ($employeeId){
                                        $query->select('laptop_id')
                                                ->from('employees_laptops')
                                                ->where('surrender_flag', 0)
                                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                                ->orWhere(function($query) use ($employeeId){
                                                    $query->where('approved_status', config('constants.APPROVED_STATUS_PENDING'))
                                                            ->where('employee_id', $employeeId);
                                                });
                                    })
                ->get()
                ->toArray();
                
    }

    static function getLaptopEmployeeDetails($id){
        return self::where('status', 1)
                    ->where('id', $id)
                    ->whereNotIn('id', function($query){
                        $query->select('laptop_id')
                                ->from('employees_laptops')
                                ->where('surrender_flag', 0)
                                ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);
                    })
            ->get()
            ->toArray();
    }

    /**
     * Retrieve laptop list for laptop list screen
     * Filters:
     * status: 1:all, 2:active, 3:inacive 
     * availability: 1:all, 2:owned, 3:not owned
     * srchFilter: 1:tag number, 2:make, 3:model, 4:processor/cpu, 5:clock speed, 6: ram
     *
     * @param string $keyword
     * @param string $availability
     * @param string $status
     * @return array
     */
    static function getLaptopList($keyword = '', $availability = '', $status = '', $srchFilter = '', $forScreen = true){
        $query = self::selectRaw('
                            l.id
                            ,el.id AS linkage_id
                            ,e.id AS linked_employee_id
                            ,e.active_status AS linked_employee_status
                            ,l.tag_number
                            ,l.peza_form_number
                            ,l.peza_permit_number
                            ,l.laptop_make
                            ,l.laptop_model
                            ,l.laptop_cpu
                            ,l.laptop_clock_speed
                            ,l.laptop_ram
                            ,CASE WHEN el.brought_home_flag THEN "Y" ELSE "N" END AS brought_home_flag
                            ,CASE WHEN el.vpn_flag THEN "Y" ELSE "N" END AS vpn_access
                            ,el.remarks
                            ,el.surrender_flag
                            ,el.update_time as last_update
                            ,CONCAT(e.last_name, ", ", e.first_name) as owner
                            ,CASE  WHEN l.status = 1 THEN "Active" ELSE "Inactive" END AS status')
                        ->from('laptops AS l')
                        ->leftJoin('employees_laptops AS el', function ($join) use ($forScreen) {
                            $join->on('el.laptop_id', 'l.id')
                                    ->whereIn('el.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                                    ->where('el.surrender_flag', 0);
                        })
                        ->leftJoin('employees AS e', function ($join) {
                            $join->on('e.id', 'el.employee_id')
                                    ->where('e.active_status', 1)
                                    ->whereIn('e.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);
                        });

        if(!empty($status) && $status != 1){
            if($status == 2){ 
                $query->where('l.status', 1);
            }elseif($status == 3){
                $query->where('l.status', 0);
            }
        }

        if(!empty($availability) && $availability != 1){
            if($availability == 2){
                $query->whereIn('l.id', function($query){
                    $query->select('laptop_id')
                    ->from('employees_laptops')
                    ->where('surrender_flag', 0)
                    ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);
                });
            }elseif($availability == 3){
                $query->whereNotIn('l.id', function($query){
                    $query->select('laptop_id')
                    ->from('employees_laptops')
                    ->where('surrender_flag', 0)
                    ->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);
                });
            }
        }

        if(!empty($keyword) && !empty($srchFilter) && in_array($srchFilter, array_keys(self::LAPTOP_SEARCH_FILTER))){
            $query->where('l.' .self::LAPTOP_SEARCH_FILTER[$srchFilter], 'LIKE', "%{$keyword}%");
        }
        
        $query->whereIn('l.approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);

        if($forScreen){
            $query->orderBy('tag_number', 'asc');
        }else{
            //order by for download
            $query->orderByRaw('CASE WHEN el.id is NULL or e.id is NULL THEN 1 ELSE 0 END ASC')
                    ->orderBy('e.last_name', 'asc')
                    ->orderBy('e.first_name', 'asc')
                    ->orderBy('el.surrender_flag', 'asc')
                    ->orderBy('l.tag_number');
                   
        }

        return $query->get()->toArray();
    }

    /**
     * Get laptop requests
     *
     * @return array
     */
    static function getLaptopRequest(){
        $query = self::selectRaw('
                                id,
                                tag_number,
                                peza_form_number,
                                peza_permit_number,
                                laptop_make,
                                laptop_model,
                                CASE WHEN status=1 THEN "Active" ELSE "Inactive" END as status
                            ')
                        ->whereIn('approved_status', [config('constants.APPROVED_STATUS_PENDING'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')]);


        if(Auth::user()->roles != config('constants.MANAGER_ROLE_VALUE')){
            //get all laptop request and laptop linkage requests of the current user only
            $query->where('updated_by', Auth::user()->id);
        }

        return $query->get()->toArray();
    }
}
