<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Employees;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SoftwaresController;
use App\Http\Controllers\LaptopsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ProjectsController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;
use PhpParser\Node\Expr\FuncCall;

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

Route::middleware('guest')->controller(LoginController::class)->group(function(){
    Route::match(['GET', 'POST'], 'login', 'index')->name('login');
    Route::match(['GET', 'POST'], 'forgotPassword', 'forgotPassword')->name('login.forgotPassword');
});

Route::middleware(['guest', 'web'])->controller(EmployeesController::class)->prefix('/employees')->group(function(){
    Route::post('/regist', 'regist')->name('employees.regist');
    Route::get('/create/{rejectCode?}', 'create')->name('employees.create');
    Route::get('/regist/complete', function(){
        return view('employees.complete');
    })->name('employees.regist.complete');
    
});

Route::middleware(['auth', 'web', 'isActive'])->group(function(){
    Route::get('logout', [LogoutController::class, 'execute'])->name('logout');
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::prefix('/employees')->controller(EmployeesController::class)->group(function(){
        Route::get('/',  'index')->name('employees');
        Route::get('/{id}', 'detail')->name('employees.details')->whereNumber('id');
        Route::get('/{id}/edit', 'edit')->name('employees.edit')->whereNumber('id');
        Route::get('/{id}/request', 'request')->name('employees.request')->whereNumber('id');
        Route::post('/store', 'store')->name('employees.store');
        Route::post('/reject', 'reject')->name('employees.reject');
        Route::post('/update', 'update')->name('employees.update');
        Route::get('/sendNotification',  'sendNotification')->name('employees.sendNotification');
        Route::post('/download',  'download')->name('employees.download');
        Route::get('/update/complete', function(){
            return view('employees.complete');
        })->name('employees.update.complete');
    });

    Route::prefix('/laptops')->controller(LaptopsController::class)->group(function(){
        Route::get('/', 'index')->name('laptops.index');
        Route::post('/download', 'download')->name('laptops.download');
        Route::get('/create/{rejectCode?}', 'create')->name('laptops.create');
        Route::post('/regist', 'regist')->name('laptops.regist');
        Route::get('/{id}', 'details')->name('laptops.details');
        Route::get('/{id}/request', 'request')->name('laptops.request');
        Route::post('/store', 'store')->name('laptops.store');
        Route::post('/reject', 'reject')->name('laptops.reject');
        Route::post('/storeLinkage', 'storeLinkage')->name('laptops.storeLinkage');
        Route::post('/rejectLinkage', 'rejectLinkage')->name('laptops.rejectLinkage');

        Route::get('/regist/complete', function(){
            return view('laptops.complete');
        })->name('laptops.regist.complete');
    });

    Route::prefix('/softwares')->controller(SoftwaresController::class)->group(function(){
        Route::get('/',  'index')->name('softwares');
        Route::get('/{id}/request', 'detailview')->name('softwares.request')->whereNumber('id');
        Route::post('/store', 'store')->name('softwares.store');
        Route::post('/reject', 'reject')->name('softwares.reject');
        Route::post('/update', 'update')->name('softwares.update');
        Route::get('/{id}', 'detail')->name('softwares.details')->whereNumber('id');
        Route::get('/{id}/edit', 'edit')->name('softwares.edit')->whereNumber('id');
        Route::post('/regist', 'regist')->name('softwares.regist');
        Route::get('/create/{rejectCode?}', 'create')->name('softwares.create');
        Route::get('/download',  'download')->name('softwares.download');
        Route::get('/regist/complete', function(){
            return view('softwares.complete');
        })->name('softwares.regist.complete');
        Route::get('/update/complete', function(){
            return view('softwares.complete');
        })->name('softwares.update.complete');        
    });

    Route::prefix('/projects')->controller(ProjectsController::class)->group(function(){
        Route::get('/',  'index')->name('projects');
        Route::get('/create', 'create')->name('projects.create');
        Route::post('/regist', 'regist')->name('projects.regist');
        Route::get('/{id}', 'detail')->name('projects.details')->whereNumber('id');
        Route::get('/{id}/edit', 'edit')->name('projects.edit')->whereNumber('id');
        Route::post('/store', 'store')->name('projects.store');
        Route::post('/removeSoftware', 'removeLinkedSoftwareToProject')->name('projects.removeSoftware');
        Route::get('/{id}/request', 'detailview')->name('projects.request')->whereNumber('id');
        Route::post('/storeLinkage', 'storeLinkage')->name('projects.storeLinkage');
        Route::post('/rejectLinkage', 'rejectLinkage')->name('projects.rejectLinkage');

    });

    Route::prefix('/servers')->group(function(){
        Route::get('/', function(){
            return 'Welcome to Servers List'; //dummy
        })->name('servers');
        Route::get('/create', function(){
            return 'You can add server data here'; //dummy
        })->name('servers.create');
    });

});

