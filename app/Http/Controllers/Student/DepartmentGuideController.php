<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentGuideController extends Controller
{
    public function index(Request $request)
    {
        $student = auth('student')->user();

        $tree = [
            'Electrical Engineering & Computing' => [
                'Software Engineering',
                'Computer Science',
                'Electrical Engineering',
                'Electronics & Communication Engineering',
                'Electrical Power & Control Engineering',
            ],
            'Mechanical, Chemical & Materials Engineering' => [
                'Mechanical Engineering',
                'Chemical Engineering',
                'Materials Science & Engineering',
            ],
            'Civil Engineering & Architecture' => [
                'Civil Engineering',
                'Architecture',
                'Construction Technology',
                'Urban Planning',
                'Water Resources',
                'Geomatics',
            ],
            'Applied Natural Science' => [
                'Applied Mathematics',
                'Applied Physics',
                'Applied Chemistry',
                'Applied Biology',
                'Applied Geology',
                'Industrial Chemistry',
                'Pharmacy',
            ],
            'Humanities & Social Sciences' => [
                'Economics',
                'Psychology',
                'Sociology',
                'Civics',
                'Communication',
            ],
        ];

        $deptLower = strtolower($student->department ?? '');
        $currentYear = (int) ($student->current_year ?? 1);

        if ($currentYear === 1) {
            // For first year, show all relevant schools and their departments
            if ($deptLower === 'pre-engineering') {
                $schools = [
                    'Electrical Engineering & Computing',
                    'Mechanical, Chemical & Materials Engineering',
                    'Civil Engineering & Architecture',
                ];
            } elseif ($deptLower === 'pre-science' || $deptLower === 'pre-applied natural science') {
                $schools = [
                    'Applied Natural Science',
                    'Humanities & Social Sciences',
                ];
            } else {
                $schools = [];
            }

            $groupedDepartments = [];
            foreach ($schools as $school) {
                $departments = Department::whereIn('name', $tree[$school] ?? [])->get()->map(function ($dept) use ($student) {
                    $cgpa = $student->cgpa ?? 0;
                    $min_gpa = $dept->min_gpa ?? 0;
                    if ($min_gpa == 0) {
                        $chance = 99;
                    } elseif ($cgpa < $min_gpa) {
                        $chance = 0;
                    } elseif ($cgpa == $min_gpa) {
                        $chance = 40;
                    } else {
                        $diff = $cgpa - $min_gpa;
                        if ($diff >= 0.5) {
                            $chance = 95;
                        } else {
                            $chance = 40 + ($diff / 0.5) * 55;
                        }
                    }
                    $dept->chance = round($chance);
                    return $dept;
                });
                $groupedDepartments[$school] = $departments;
            }
            return view('student.department-guide', [
                'groupedDepartments' => $groupedDepartments,
                'isFirstYear' => true,
            ]);
        } else {
            // Year 2+ rules: Remove the schools, ONLY show departments natively mapped to that school.
            $schoolKeys = [
                'electrical engineering & computing' => 'Electrical Engineering & Computing',
                'mechanical, chemical & materials engineering' => 'Mechanical, Chemical & Materials Engineering',
                'civil engineering & architecture' => 'Civil Engineering & Architecture',
                'applied natural science' => 'Applied Natural Science',
                'humanities & social sciences' => 'Humanities & Social Sciences'
            ];

            $departments = collect();
            if (isset($schoolKeys[$deptLower])) {
                $departments = Department::whereIn('name', $tree[$schoolKeys[$deptLower]] ?? [])->get()->map(function ($dept) use ($student) {
                    $cgpa = $student->cgpa ?? 0;
                    $min_gpa = $dept->min_gpa ?? 0;
                    if ($min_gpa == 0) {
                        $chance = 99;
                    } elseif ($cgpa < $min_gpa) {
                        $chance = 0;
                    } elseif ($cgpa == $min_gpa) {
                        $chance = 40;
                    } else {
                        $diff = $cgpa - $min_gpa;
                        if ($diff >= 0.5) {
                            $chance = 95;
                        } else {
                            $chance = 40 + ($diff / 0.5) * 55;
                        }
                    }
                    $dept->chance = round($chance);
                    return $dept;
                });
            }
            return view('student.department-guide', [
                'departments' => $departments,
                'isFirstYear' => false,
            ]);
        }
    }
}
