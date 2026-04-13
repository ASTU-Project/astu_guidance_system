<?php

namespace App\Mcp\Tools;

use App\Models\User;
use App\Models\Student;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('student.list')]
#[Description('List/search students. Supports optional filters + pagination to avoid large payloads.')]
class StudentList extends Tool
{
    private const MAX_LIMIT = 25;

    private function param(Request $request, string $key, mixed $default = null): mixed
    {
        if (method_exists($request, 'input')) {
            return $request->input($key, $default);
        }

        if (method_exists($request, 'get')) {
            return $request->get($key, $default);
        }

        if ($request instanceof \ArrayAccess && isset($request[$key])) {
            return $request[$key];
        }

        if (isset($request->{$key})) {
            return $request->{$key};
        }

        return $default;
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $user = $request->get('user') ?? $request->user();

        if (! $user instanceof User) {
            return Response::error('Unauthenticated');
        }

        if (! $this->isAdmin($user)) {
            return Response::error('Forbidden: admin access required for student tool.');
        }

        $q = trim((string) $this->param($request, 'q', ''));
        $department = trim((string) $this->param($request, 'department', ''));
        $minCgpa = $this->param($request, 'min_cgpa');
        $cursorId = $this->param($request, 'cursor_id');
        $limit = (int) $this->param($request, 'limit', 10);
        $limit = max(1, min(self::MAX_LIMIT, $limit));

        $query = Student::query()
            ->select([
                'id',
                'name',
                'student_id',
                'department',
                'current_semester',
                'current_year',
                'current_section',
                'cgpa',
            ])
            ->orderBy('id', 'asc');

        if ($cursorId !== null && is_numeric($cursorId)) {
            $query->where('id', '>', (int) $cursorId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('student_id', 'like', '%'.$q.'%');
            });
        }

        if ($department !== '') {
            $query->where('department', $department);
        }

        if ($minCgpa !== null && is_numeric($minCgpa)) {
            $query->where('cgpa', '>=', (float) $minCgpa);
        }

        $rows = $query->limit($limit + 1)->get();

        $hasMore = $rows->count() > $limit;
        $students = $rows->take($limit)->map(function (Student $student): array {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'student_id' => $student->student_id,
                'department' => $student->department,
                'current_year' => $student->current_year,
                'current_semester_current_section' => trim($student->current_semester.' '.$student->current_section),
                'cgpa' => $student->cgpa,
            ];
        })->values();

        $nextCursorId = null;
        if ($hasMore && $students->isNotEmpty()) {
            $nextCursorId = $students->last()['id'] ?? null;
        }

        return Response::json([
            'students' => $students->toArray(),
            'count' => $students->count(),
            'next_cursor_id' => $nextCursorId,
        ]);
    }

    /**
     * Define the input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'q' => ['type' => 'string', 'description' => 'Search by name or student_id.'],
                'department' => ['type' => 'string', 'description' => 'Filter by department name/code as stored.'],
                'min_cgpa' => ['type' => 'number', 'description' => 'Minimum CGPA filter.'],
                'limit' => ['type' => 'integer', 'description' => 'Max rows to return (1-25). Defaults to 10.'],
                'cursor_id' => ['type' => 'integer', 'description' => 'Return rows with id > cursor_id (pagination).'],
            ],
            'additionalProperties' => false,
        ];
    }

    private function isAdmin(User $user): bool
    {
        if (method_exists($user, 'isAdmin')) {
            return (bool) $user->isAdmin();
        }

        if (isset($user->is_admin)) {
            return (bool) $user->is_admin;
        }

        if (isset($user->role)) {
            return strtolower((string) $user->role) === 'admin';
        }

        if (isset($user->user_type)) {
            return strtolower((string) $user->user_type) === 'admin';
        }

        // Fallback for projects without explicit role schema yet.
        return (int) $user->id === 1;
    }
}
