<?php

use App\Http\Controllers\Employees;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
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

Route::middleware(['guest', 'web'])->controller(EmployeesController::class)->group(function(){
    Route::match(['GET', 'POST'], '/employees/regist', 'regist')->name('employees.regist');
    Route::get('/employees/regist/complete', function(){
        return view('employees.complete');
    })->name('employees.regist.complete');
    
});

Route::middleware('api')->group(function(){
    Route::post('/employees/changePassword', [EmployeesController::class, 'changePassword'])->name('employees.changePassword');
});


Route::middleware(['auth', 'web'])->group(function(){
    Route::get('logout', [LogoutController::class, 'execute'])->name('logout');
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::prefix('/employees')->controller(EmployeesController::class)->group(function(){
        Route::get('/', function(){
            return 'Welcome to employees List'; //dummy
        })->name('employees');
        Route::get('/{id}', 'detail')->name('employees.details')->whereNumber('id');
        Route::get('/{id}/edit', 'edit')->name('employees.edit')->whereNumber('id');
        Route::get('/{id}/request', 'request')->name('employees.request')->whereNumber('id');
    });

    Route::prefix('/laptops')->group(function(){
        Route::get('/', function(){
            return 'Welcome to Laptop List';
        })->name('laptops');
        Route::get('/create', function(){
            return 'You can add laptop data here'; //dummy
        })->name('laptops.create');
    });

    Route::prefix('/softwares')->group(function(){
        Route::get('/', function(){
            return 'Welcome to Softwares List'; //dummy
        })->name('softwares');
        Route::get('/create', function(){
            return 'You can add software data here'; //dummy
        })->name('softwares.create');
    });

    Route::prefix('/projects')->group(function(){
        Route::get('/', function(){
            return 'Welcome to projects List'; //dummy
        })->name('projects');
        Route::get('/create', function(){
            return 'You can add projects data here'; //dummy
        })->name('projects.create');
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

