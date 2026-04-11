<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\MapLocationController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
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
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {
        Route::resource('students', StudentController::class);
    });

    Route::get('/admin/calendar', function () {
        return view('admin.calendar');
    })->name('admin.calendar');

    Route::get('/admin/map', [MapLocationController::class, 'index'])->name('admin.map');
    Route::post('/admin/map', [MapLocationController::class, 'store'])->name('admin.map.store');
    Route::delete('/admin/map/{location}', [MapLocationController::class, 'destroy'])->name('admin.map.destroy');

    Route::get('/admin/policy', function () {
        return view('admin.policy');
    })->name('admin.policy');

    Route::get('/admin/blog', function () {
        return view('admin.blog');
    })->name('admin.blog');

    Route::get('/admin/departments', [DepartmentController::class, 'index'])->name('admin.departments');
    Route::post('/admin/departments', [DepartmentController::class, 'add'])->name('admin.departments.store');

    Route::get('/admin/message', function () {
        return view('admin.message');
    })->name('admin.message');

    Route::get('/admin/automate', function () {
        return view('admin.automate');
    })->name('admin.automate');


});