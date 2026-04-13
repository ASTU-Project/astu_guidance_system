<?php

namespace App\Mcp\Tools;

use App\Models\Student;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('student.list')]
#[Description('List students with name, student_id, phone, email, department, current_semester_current_section, and cgpa. No input parameters.')]
class StudentList extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $user = $request->user();

        if (! $user) {
            return Response::error('Unauthenticated');
        }

        $students = Student::query()
            ->select([
                'name',
                'student_id',
                'phone',
                'email',
                'department',
                'current_semester',
                'current_year',
                'current_section',
                'cgpa',
            ])
            ->orderBy('name', 'asc')
            ->get()
            ->map(function (Student $student): array {
                return [
                    'name' => $student->name,
                    'student_id' => $student->student_id,
                    'phone' => $student->phone,
                    'email' => $student->email,
                    'department' => $student->department,
                    'current_year' => $student->current_year,
                    'current_semester_current_section' => trim($student->current_semester.' '.$student->current_section),
                    'cgpa' => $student->cgpa,
                ];
            })
            ->values();

        return Response::json([
            'students' => $students->toArray(),
            'count' => $students->count(),
        ]);
    }

    /**
     * Define the input schema - no parameters needed.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
