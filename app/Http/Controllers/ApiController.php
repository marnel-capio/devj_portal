<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employees;
use PhpParser\Node\Stmt\Return_;
use DB;

class ApiController extends Controller
{
    public function getEmployeeByFilter(Request $request){
        $searchFilter = [
            'keyword' => $request->get('keyword'),
            'filter' => $request->get('filter'),
        ];
        // DB::enableQueryLog();
        $employee = Employees::where(function($query) {
            $query->where('approved_status', '!=' ,3)
                    ->orWhere(function($query) {
                        $query->where('active_status', 0)
                                ->where('approved_status', '!=', 1);
                    });
                })
                ->orderBy('last_name', 'ASC')
                ->get();

       // get employees
        if (!empty($searchFilter['keyword'])) {
            if ($searchFilter['filter'] == 1) {
                $employee = Employees::where(function($query) {
                    $query->where('approved_status', '!=' ,3)
                            ->orWhere(function($query) {
                                $query->where('active_status', 0)
                                        ->where('approved_status', '!=', 1);
                            });
                    })
                    ->where(function($query) use ($searchFilter) {
                        $query->where('first_name','LIKE','%'.$searchFilter['keyword'].'%')
                                ->orWhere('last_name','LIKE','%'.$searchFilter['keyword'].'%')
                                ->orWhere('middle_name','LIKE','%'.$searchFilter['keyword'].'%');
                    })
                    ->orderBy('last_name', 'ASC')
                    ->get();
            } else if ($searchFilter['filter'] == 2) {
                $employee = Employees::where(function($query) {
                    $query->where('approved_status', '!=' ,3)
                            ->orWhere(function($query) {
                                $query->where('active_status', 0)
                                        ->where('approved_status', '!=', 1);
                            });
                    })
                    ->where('current_address_city','LIKE','%'.$searchFilter['keyword'].'%')
                    ->orderBy('last_name', 'ASC')
                    ->get();
            } else if ($searchFilter['filter'] == 3) {
                $employee = Employees::where(function($query) {
                    $query->where('approved_status', '!=' ,3)
                            ->orWhere(function($query) {
                                $query->where('active_status', 0)
                                        ->where('approved_status', '!=', 1);
                            });
                    })
                    ->where('current_address_province','LIKE','%'.$searchFilter['keyword'].'%')
                    ->orderBy('last_name', 'ASC')
                    ->get();
            }
        }
        // $query = DB::getQueryLog();
        // dd($query);
        return json_encode($employee);
    }
}
