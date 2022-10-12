<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
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

Route::match(['GET', 'POST'], 'login', [LoginController::class, 'index'])->name('login')->middleware(RedirectIfAuthenticated::class);
Route::match(['GET', 'POST'], 'forgotPassword', [LoginController::class, 'forgotPassword'])->name('login.forgotPassword')->middleware(RedirectIfAuthenticated::class);
Route::get('/logout', [LogoutController::class, 'execute'])->name('logout')->middleware(Authenticate::class);



Route::get('/', [HomeController::class, 'index'])->name('home')->middleware(Authenticate::class);
