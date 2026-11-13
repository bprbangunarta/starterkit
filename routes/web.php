<?php

use App\Enums\RoleName;
use App\Enums\SettingKey;
use App\Enums\StorageType;
use App\Http\Controllers\Audit\AuditLogController;
use App\Http\Controllers\Audit\AuditLogExportController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordExpiredController;
use App\Http\Controllers\ErrorPreviewController;
use App\Http\Controllers\Notifications\AllNotificationReadController;
use App\Http\Controllers\Notifications\NotificationReadController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\Profile\PasswordController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Settings\BrandImageController;
use App\Http\Controllers\Settings\SettingController;
use App\Http\Controllers\Settings\SettingImageController;
use App\Http\Controllers\Storage\StorageController;
use App\Http\Controllers\Storage\StorageFileController;
use App\Http\Controllers\Storage\StorageFileDownloadController;
use App\Http\Controllers\Storage\StorageFilePreviewController;
use App\Http\Controllers\Storage\StorageFolderController;
use App\Http\Controllers\Storage\StorageNameController;
use App\Http\Controllers\Storage\StorageTestController;
use App\Http\Controllers\Users\RestoredUserController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\UserPasswordResetController;
use App\Http\Middleware\EnsurePasswordIsConfirmed;
use App\Http\Middleware\EnsurePasswordIsCurrent;
use Illuminate\Support\Facades\Route;

// Public: the sign-in page shows the logo.
Route::get('/brand/{kind}', [BrandImageController::class, 'show'])->whereIn('kind', SettingKey::imageValues())->middleware('throttle:120,1')->name('brand.image');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:20,1')->name('login.store');
});

Route::middleware(['auth', 'auth.session', EnsurePasswordIsCurrent::class])->group(function () {
    Route::inertia('/', 'dashboard')->name('dashboard');
    Route::inertia('/ui-guideline', 'ui-guideline')->name('ui-guideline');
    Route::get('/ui-guideline/errors/{status}', [ErrorPreviewController::class, 'show'])->whereIn('status', ErrorPreviewController::STATUSES)->name('ui-guideline.errors.show');
    Route::post('/notifications/read-all', [AllNotificationReadController::class, 'store'])->middleware('throttle:60,1')->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationReadController::class, 'store'])->middleware('throttle:60,1')->name('notifications.read');
    Route::post('/confirm-password', [ConfirmPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.confirm.store');
    Route::get('/password-expired', [PasswordExpiredController::class, 'show'])->name('password.expired');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile/password', [PasswordController::class, 'update'])->middleware('throttle:6,1')->name('profile.password.update');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // The first features are run by the Super Admin only (D-0029); finer permissions come with the business features.
    Route::middleware('role:'.RoleName::SuperAdmin->value)->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');
        Route::post('/audit-logs/exports', [AuditLogExportController::class, 'store'])->middleware('throttle:6,1')->name('audit-logs.exports.store');
        Route::get('/audit-logs/exports/{export}', [AuditLogExportController::class, 'show'])->name('audit-logs.exports.show');
        Route::get('/storage', [StorageController::class, 'index'])->name('storage');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');
        Route::get('/storage/{type}/files', [StorageFileController::class, 'index'])->name('storage.files');
        Route::get('/storage/{type}/files/preview', [StorageFilePreviewController::class, 'show'])->middleware('throttle:60,1')->name('storage.files.preview');
        Route::get('/storage/{type}/files/download', [StorageFileDownloadController::class, 'show'])->middleware('throttle:30,1')->name('storage.files.download');
        Route::get('/offices', [OfficeController::class, 'index'])->name('offices');
        Route::get('/roles', [RoleController::class, 'index'])->name('roles');
        Route::get('/users', [UserController::class, 'index'])->name('users');

        // Changing who may sign in or what they may do needs a recent password confirmation (D-0033).
        Route::middleware([EnsurePasswordIsConfirmed::class, 'throttle:60,1'])->group(function () {
            // PATCH, not PUT: the framework's own `storage/{path}` route of the local disk already answers PUT.
            Route::patch('/storage/{type}', [StorageController::class, 'update'])->whereIn('type', StorageType::configurableValues())->name('storage.update');
            Route::delete('/storage/{type}', [StorageController::class, 'destroy'])->whereIn('type', StorageType::configurableValues())->name('storage.destroy');
            Route::patch('/settings', [SettingController::class, 'update'])->name('settings.update');
            Route::post('/settings/images/{kind}', [SettingImageController::class, 'store'])->whereIn('kind', SettingKey::imageValues())->name('settings.images.store');
            Route::delete('/settings/images/{kind}', [SettingImageController::class, 'destroy'])->whereIn('kind', SettingKey::imageValues())->name('settings.images.destroy');
            Route::patch('/storage/{type}/name', [StorageNameController::class, 'update'])->name('storage.name.update');
            Route::post('/storage/{type}/folders', [StorageFolderController::class, 'store'])->name('storage.folders.store');
            Route::post('/storage/{type}/files', [StorageFileController::class, 'store'])->name('storage.files.store');
            Route::patch('/storage/{type}/files', [StorageFileController::class, 'update'])->name('storage.files.update');
            Route::delete('/storage/{type}/files', [StorageFileController::class, 'destroy'])->name('storage.files.destroy');
            Route::post('/storage/{type}/test', [StorageTestController::class, 'store'])->middleware('throttle:10,1')->name('storage.test.store');
            Route::post('/offices', [OfficeController::class, 'store'])->name('offices.store');
            Route::put('/offices/{office}', [OfficeController::class, 'update'])->name('offices.update');
            Route::delete('/offices/{office}', [OfficeController::class, 'destroy'])->name('offices.destroy');
            Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
            Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::post('/users/{user}/password-reset', [UserPasswordResetController::class, 'store'])->name('users.password-reset');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('/users/{user}/restore', [RestoredUserController::class, 'store'])->withTrashed()->name('users.restore');
        });
    });
});
