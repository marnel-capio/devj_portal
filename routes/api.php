<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Route::middleware('api')->controller(ApiController::class)->prefix('/api')->group(function(){
//     Route::post('/changePassword', 'changePassword')->name('api.changePassword');
//     Route::post('/linkLaptop', 'linkLaptop')->name('api.linkLaptop');
//     Route::post('/linkProject', 'linkProject')->name('api.linkProject');
// });

 // Route::get('/employees/search', [ApiController::class, 'getEmployeeByFilter'])->name('api');