<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Event;
use App\Models\Grade;
use App\Models\Student;

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

        $performance_chart = Grade::query()
            ->selectRaw('year, AVG(score) as avg_score')
            ->groupBy('year')
            ->orderByDesc('year')
            ->limit(6)
            ->get()
            ->sortBy('year')
            ->values()
            ->map(function ($row) {
                $gpa = ((float) $row->avg_score) / 25;

                return [
                    'year' => (int) $row->year,
                    'gpa' => round(max(0, min(4, $gpa)), 2),
                ];
            });
       
        return view('admin.dashboard', [
            'number_of_students' => $number_of_students,
            'number_of_departments' => $number_of_departments,
            'number_of_events' => $number_of_events,
            'top_departments' => $top_departments,
            'performance_chart' => $performance_chart,
        ]);
    }
}
