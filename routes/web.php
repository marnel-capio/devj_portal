<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('login', [LoginController::class, 'index'])->name('login');
Route::post('login/execute', [LoginController::class, 'execute'])->name('login.execute');
Route::get('forgotPassword', [LoginController::class, 'forgotPassword'])->name('login.forgotPassword');
Route::post('forgotPassword/reset', [LoginController::class], 'resetPassword')->name('login.resetPassword');

Route::get('/', function(){
    return(dd("welcame to home page!"));
});
