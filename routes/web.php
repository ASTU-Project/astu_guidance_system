<?php

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
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/students', function () {
        return view('admin.students');
    })->name('admin.students');

    Route::get('/admin/calendar', function () {
        return view('admin.calendar');
    })->name('admin.calendar');

    Route::get('/admin/map', function () {
        return view('admin.map');
    })->name('admin.map');

    Route::get('/admin/policy', function () {
        return view('admin.policy');
    })->name('admin.policy');

    Route::get('/admin/blog', function () {
        return view('admin.blog');
    })->name('admin.blog');

    Route::get('/admin/departments', function () {
        return view('admin.departments');
    })->name('admin.departments');

    Route::get('/admin/message', function () {
        return view('admin.message');
    })->name('admin.message');

    Route::get('/admin/automate', function () {
        return view('admin.automate');
    })->name('admin.automate');

});