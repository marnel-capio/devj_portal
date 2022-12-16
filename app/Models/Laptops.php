<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Laptops extends Model
{
    use HasFactory;

    const UPDATED_AT = 'update_time';
    const CREATED_AT = 'create_time';
    protected $guarded = [];

    static function getLaptopDropdown(){

        return self::select('id', 'tag_number', )
                ->where('status', 1)
                ->whereNotIn('id', function($query){
                                        $query->select('laptop_id')
                                                ->from('employees_laptops')
                                                ->where('surrender_flag', 0)
                                                ->where('approved_status', 2);
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
                                ->where('approved_status', 2);
                    })
            ->get()
            ->toArray();
    }

    /**
     * Rerieve laptop list for laptop list screen
     * Filters:
     * status: 1:all, 2:active, 3:inacive 
     * availability: 1:all, 2:owned, 3:not owned
     *
     * @param string $keyword
     * @param string $availability
     * @param string $status
     * @return void
     */
    static function getLaptopList($keyword = '', $availability = '', $status = ''){
        $query = self::selectRaw('
                            id
                            ,tag_number
                            ,peza_form_number
                            ,peza_permit_number
                            ,laptop_make
                            ,laptop_model
                            ,CASE  WHEN status = 1 THEN "Active" ELSE "Inactive" END AS status');

        if(!empty($status) && $status != 1){
            if($status == 2){ 
                $query->where('status', 1);
            }elseif($status == 3){
                $query->where('status', 0);
            }
        }

        if(!empty($availability) && $availability != 1){
            if($availability == 2){
                $query->whereIn('id', function($query){
                    $query->select('laptop_id')
                    ->from('employees_laptops')
                    ->where('surrender_flag', 0)
                    ->where('approved_status', config('constants.APPROVED_STATUS_APPROVED'));
                });
            }elseif($availability == 3){
                $query->whereNotIn('id', function($query){
                    $query->select('laptop_id')
                    ->from('employees_laptops')
                    ->where('surrender_flag', 0)
                    ->where('approved_status', config('constants.APPROVED_STATUS_APPROVED'));
                });
            }
        }

        if(!empty($keyword)){
            $query->where(function($query) use ($keyword){
                $query->where('tag_number', 'LIKE', "%{$keyword}%")
                        ->orWhere('peza_form_number', 'LIKE', "%{$keyword}%")
                        ->orWhere('peza_permit_number', 'LIKE', "%{$keyword}%")
                        ->orWhere('laptop_make', 'LIKE', "%{$keyword}%")
                        ->orWhere('laptop_model', 'LIKE', "%{$keyword}%");
            });
        }
        
        $query->whereIn('approved_status', [config('constants.APPROVED_STATUS_APPROVED'), config('constants.APPROVED_STATUS_PENDING_APPROVAL_FOR_UPDATE')])
                ->orderBy('tag_number', 'asc');

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
