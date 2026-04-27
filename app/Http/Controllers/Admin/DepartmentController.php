<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Student;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::query()
            ->orderBy('name')
            ->get()
            ->map(function ($department) {
                $department->student_count = Student::query()
                    ->where('department', $department->name)
                    ->count();

                return $department;
            });


        return view('admin.departments', compact('departments'));
    }

    // public function store(Request $request){
    //     $validated = $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'code' => ['required', 'string', 'max:20', 'unique:departments,code'],
    //         'spot_limit' => ['required', 'integer', 'min:1'],
    //         'min_gpa' => ['required', 'numeric', 'min:0', 'max:4'],
    //     ]);

    //     Department::create([
    //         'name' => $validated['name'],
    //         'code' => strtoupper($validated['code']),
    //         'spot_limit' => $validated['spot_limit'],
    //         'min_gpa' => $validated['min_gpa'],
    //     ]);

    //     return redirect()
    //         ->route('admin.departments')
    //         ->with('success', 'Department created successfully.');
    // }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'description' => ['nullable', 'string'],
        ]);

        $department->update([
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Department description updated successfully.');
    }
}
