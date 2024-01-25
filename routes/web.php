<?php

use App\Http\Controllers\admin\PermissionController;
use App\Http\Controllers\admin\RoleController;
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
                Route::post('/user/add', 'create')->name('admin.user.create');
                Route::put('/user/update', 'update')->name('admin.user.update');
                Route::delete('/user/{id}/destroy', 'destroy')->name('admin.user.destroy');
                Route::put('/user/reset-password/{id}', 'reset_password')->name('admin.user.reset_password');
            });

            Route::controller(RoleController::class)->group(function () {
                Route::get('/role', 'index')->name('admin.role.index');
                Route::post('/role/add', 'create')->name('admin.role.create');
                Route::put('/role/update', 'update')->name('admin.role.update');
                Route::delete('/role/destroy', 'destroy')->name('admin.role.destroy');
            });

            Route::controller(PermissionController::class)->group(function () {
                Route::get('/permission', 'index')->name('admin.permission.index');
                Route::post('/permission/add', 'create')->name('admin.permission.create');
                Route::put('/permission/update', 'update')->name('admin.permission.update');
                Route::delete('/permission/destroy', 'destroy')->name('admin.permission.destroy');

                Route::post('/permission/assign', 'assign')->name('admin.permission.assign');
                Route::get('/permission/sync/{role}/edit', 'sync_edit')->name('admin.permission.assign.edit');
                Route::put('/permission/sync/{role}/update', 'sync_update')->name('admin.permission.assign.update');
            });
        });
    });
});
