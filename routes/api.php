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

Route::middleware('auth:sanctum')->controller(ApiController::class)->group(function(){
    Route::post('/changePassword', 'changePassword')->name('api.changePassword');
    Route::post('/linkLaptop', 'linkLaptop')->name('api.linkLaptop');
    Route::post('/linkProjectToEmployee', 'linkProjectToEmployee')->name('api.linkProjectToEmployee');
    Route::post('/linkEmployeeToProject', 'linkEmployeeToProject')->name('api.linkEmployeeToProject');
    Route::post('/updateEmployeeProjectLinkage', 'updateEmployeeProjectLinkage')->name('api.updateEmployeeProjectLinkage');
    Route::get('/softwares/search', 'getSoftwareByFilter')->name('api.softwaresearch');
    Route::post('/softwarelinkProject', 'softwarelinkProject')->name('api.softwarelinkProject');
    Route::get('/employees/search', 'getEmployeeByFilter')->name('api.filterEmployee');
    Route::post('/deactivateEmployee', 'deactivateEmployee');
    Route::post('/cancelEmployeeDetails', 'cancelEmployeeDetails');
    Route::post('/cancelLaptopRegister', 'cancelLaptopRegister');
    Route::post('/cancelLaptopUpdate', 'cancelLaptopUpdate');
    Route::post('/cancelEmployeeLaptopUpdate', 'cancelEmployeeLaptopUpdate');
    Route::post('/cancelEmployeeLaptopLink', 'cancelEmployeeLaptopLink');
    Route::post('/cancelSoftwareRegister', 'cancelSoftwareRegister');
    Route::post('/cancelEmployeeProjectLinkage', 'cancelEmployeeProjectLinkage');
    Route::post('/cancelEmployeeProjectUpdateLinkage', 'cancelEmployeeProjectUpdateLinkage');
    Route::post('/reactivateEmployee', 'reactivateEmployee');
    Route::post('/transferEmployee', 'transferEmployee');
    Route::post('/reinstateEmployee', 'reinstateEmployee');
    Route::get('/laptops/search', 'filterLaptopList')->name('api.filterLaptop');
    Route::post('/laptops/update', 'updateLaptopDetails')->name('api.updateLaptop');
    Route::post('/laptops/updateLinkage', 'updateLaptopLinkage')->name('api.updateLaptopLinkage');
    Route::post('/laptops/registLinkage', 'registLaptopLinkage')->name('api.registLaptopLinkage');
    Route::post('/linkSoftwareToProject', 'linkSoftwareToProject')->name('api.linkSoftwareToProject');
    Route::get('/projects/search', 'getProjectByFilter')->name('api.filterProject');
    Route::get('/servers/delete', 'deleteServer');
});

// Route::get('/getCities', "ApiController@getCities");

Route::controller(ApiController::class)->group(function () {
    Route::get('/getCities', 'getCities');
});
