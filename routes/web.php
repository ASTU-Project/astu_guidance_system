<?php

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CalendarEventController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\MapLocationController;
use App\Http\Controllers\Admin\PolicyRuleController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {
        Route::resource('students', StudentController::class);
    });

    Route::get('/admin/calendar', [CalendarController::class, 'index'])->name('admin.calendar.index');
    Route::post('/admin/calendar', [CalendarController::class, 'store'])->name('admin.calendar.store');
    Route::get('/admin/calendar/Events/{id}', [CalendarEventController::class, 'index'])->name('admin.calendar.events');
    Route::post('/admin/calendar/Events/{id}', [CalendarEventController::class, 'store'])->name('admin.calendar.events.store');
    Route::put('/admin/calendar/Events/{id}/{event}', [CalendarEventController::class, 'update'])->name('admin.calendar.events.update');

    Route::get('/admin/map', [MapLocationController::class, 'index'])->name('admin.map');
    Route::post('/admin/map', [MapLocationController::class, 'store'])->name('admin.map.store');
    Route::delete('/admin/map/{location}', [MapLocationController::class, 'destroy'])->name('admin.map.destroy');
    Route::put('/admin/map/{location}', [MapLocationController::class, 'update'])->name('admin.map.update');

    Route::get('/admin/policy', [PolicyRuleController::class, 'index'])->name('admin.policy');
    Route::post('/admin/policy', [PolicyRuleController::class, 'store'])->name('admin.policy.store');
    Route::put('/admin/policy/{policy}', [PolicyRuleController::class, 'update'])->name('admin.policy.update');
    Route::delete('/admin/policy/{policy}', [PolicyRuleController::class, 'destroy'])->name('admin.policy.destroy');

    Route::get('/admin/departments', [DepartmentController::class, 'index'])->name('admin.departments');
    // Route::post('/admin/departments', [DepartmentController::class, 'store'])->name('admin.departments.store');

    Route::get('/admin/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/admin/profile', [ProfileController::class, 'updateProfile'])->name('admin.profile.update');
    Route::put('/admin/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password.update');

    Route::get('/admin/automate', function () {
        return view('admin.automate');
    })->name('admin.automate');


});