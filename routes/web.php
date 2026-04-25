<?php

use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\CalendarEventController;
use App\Http\Controllers\Admin\AdminChatController;
use App\Http\Controllers\Admin\AutomationSettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\MapLocationController;
use App\Http\Controllers\Admin\PolicyRuleController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Student\CalendarController as StudentCalendarController;
use App\Http\Controllers\Student\NavigateController as StudentNavigateController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\StudentChatController;
use App\Http\Controllers\Student\StatusController as StudentStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['guest:web'])->group(function () {
    Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
});

Route::middleware(['guest:student'])->group(function () {
    Route::get('/login', [StudentLoginController::class, 'showLoginForm'])->name('student.login');
    Route::post('/login', [StudentLoginController::class, 'login'])->name('student.login.submit');
    Route::get('/student/login', [StudentLoginController::class, 'showLoginForm']);
    Route::post('/student/login', [StudentLoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth:web'])->group(function () {
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
    Route::get('/admin/subjects', [SubjectController::class, 'index'])->name('admin.subjects.index');
    Route::get('/admin/community', [CommunityController::class, 'index'])->name('admin.community.index');
    Route::post('/admin/community', [CommunityController::class, 'store'])->name('admin.community.store');
    Route::put('/admin/community/{community}', [CommunityController::class, 'update'])->name('admin.community.update');
    Route::delete('/admin/community/{community}', [CommunityController::class, 'destroy'])->name('admin.community.destroy');
    // Route::post('/admin/departments', [DepartmentController::class, 'store'])->name('admin.departments.store');

    Route::get('/admin/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/admin/profile', [ProfileController::class, 'updateProfile'])->name('admin.profile.update');
    Route::put('/admin/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password.update');

    Route::get('/admin/automation-settings', [AutomationSettingController::class, 'show'])->name('admin.automation-settings.show');
    Route::put('/admin/automation-settings', [AutomationSettingController::class, 'update'])->name('admin.automation-settings.update');

    Route::get('/admin/automate', function () {
        return view('admin.automate');
    })->name('admin.automate');
    Route::post('/admin/automate/chat', AdminChatController::class)->name('admin.automate.chat');


});

Route::middleware(['auth:student'])->group(function () {
    Route::get('/student/dashboard', function () {
        return view('student.dashboard');
    })->name('student.dashboard');

    Route::get('/student/status', [StudentStatusController::class, 'index'])->name('student.status');

    Route::get('/student/calendar', [StudentCalendarController::class, 'index'])->name('student.calendar');
    Route::post('/student/calendar', [StudentCalendarController::class, 'store'])->name('student.calendar.store');
    Route::put('/student/calendar/{event}', [StudentCalendarController::class, 'update'])->name('student.calendar.update');

    Route::get('/student/navigate', [StudentNavigateController::class, 'index'])->name('student.navigate');

    Route::get('/student/department-guide', function () {
        return view('student.department-guide');
    })->name('student.department-guide');

    Route::get('/student/community', function () {
        return view('student.community');
    })->name('student.community');

    Route::get('/student/ai-assistant', function () {
        return view('student.ai-assistant');
    })->name('student.ai-assistant');
    Route::post('/student/ai-assistant/chat', StudentChatController::class)->name('student.ai-assistant.chat');

    Route::get('/student/profile', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
    Route::put('/student/profile', [StudentProfileController::class, 'updateProfile'])->name('student.profile.update');
    Route::put('/student/profile/password', [StudentProfileController::class, 'updatePassword'])->name('student.profile.password.update');

    Route::post('/student/logout', [StudentLoginController::class, 'logout'])->name('student.logout');
});