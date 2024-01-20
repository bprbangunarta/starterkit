<?php

use App\Http\Controllers\admin\SiteController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('/user/account', 'account')->name('user.account');
        Route::put('/user/account', 'update_account')->name('user.update.account');
        Route::put('/user/deactivation', 'deactivation_account')->name('user.deactivation');
        Route::get('/user/changer/password', 'password')->name('user.password');
        Route::put('/user/changer/password', 'update_password')->name('user.update.password');
    });

    Route::group(['middleware' => ['role:Administrator']], function () {
        Route::prefix('admin')->group(function () {
            Route::controller(SiteController::class)->group(function () {
                Route::get('/site', 'index')->name('site.index');
                Route::put('/site/update', 'update')->name('site.update');
            });

            Route::controller(AdminUserController::class)->group(function () {
                Route::get('/user', 'index')->name('admin.user.index');
            });
        });
    });
});
