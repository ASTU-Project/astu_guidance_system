<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Event;
use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        $number_of_students = Student::count();
        $number_of_departments = Department::count();
        $number_of_events = Event::count();

        $top_departments = Student::query()
            ->selectRaw('department, COUNT(*) as total_students')
            ->groupBy('department')
            ->orderByDesc('total_students')
            ->limit(3)
            ->get()
            ->map(function ($department) use ($number_of_students) {
                $department->percentage = $number_of_students > 0
                    ? round(($department->total_students / $number_of_students) * 100)
                    : 0;

                return $department;
            });
       
        return view('admin.dashboard', [
            'number_of_students' => $number_of_students,
            'number_of_departments' => $number_of_departments,
            'number_of_events' => $number_of_events,
            'top_departments' => $top_departments,
        ]);
    }
}
