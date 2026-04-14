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
#[Description('List, filter, and rank students. For list-all requests, omit q and set limit directly. For ranking requests (for example top 10 by score in year 1), use study_year + sort_by=cgpa + sort_order=desc + limit=10. current_year/study_year is academic level 1-5, not a calendar year.')]
class StudentList extends Tool
{
    private const MAX_LIMIT = 100;
    private const ALLOWED_SORT_BY = ['id', 'cgpa', 'current_year', 'name'];
    private const ALLOWED_SORT_ORDER = ['asc', 'desc'];

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
        $studyYear = $this->param($request, 'study_year');
        $minCgpa = $this->param($request, 'min_cgpa');
        $cursorId = $this->param($request, 'cursor_id');
        $sortBy = strtolower(trim((string) $this->param($request, 'sort_by', 'id')));
        $sortOrder = strtolower(trim((string) $this->param($request, 'sort_order', 'asc')));
        $limit = (int) $this->param($request, 'limit', 10);
        $limit = max(1, min(self::MAX_LIMIT, $limit));

        if (! in_array($sortBy, self::ALLOWED_SORT_BY, true)) {
            $sortBy = 'id';
        }

        if (! in_array($sortOrder, self::ALLOWED_SORT_ORDER, true)) {
            $sortOrder = 'asc';
        }

        $cursorEnabled = $sortBy === 'id';

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
            ]);

        if ($cursorEnabled && $cursorId !== null && is_numeric($cursorId)) {
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

        if ($studyYear !== null && is_numeric($studyYear)) {
            $query->where('current_year', (int) $studyYear);
        }

        if ($minCgpa !== null && is_numeric($minCgpa)) {
            $query->where('cgpa', '>=', (float) $minCgpa);
        }

        if ($sortBy === 'cgpa') {
            $query->orderByRaw('cgpa IS NULL');
        }

        $query->orderBy($sortBy, $sortOrder)->orderBy('id', 'asc');

        $rows = $query->limit($cursorEnabled ? ($limit + 1) : $limit)->get();

        $hasMore = $cursorEnabled ? ($rows->count() > $limit) : false;
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
        if ($cursorEnabled && $hasMore && $students->isNotEmpty()) {
            $nextCursorId = $students->last()['id'] ?? null;
        }

        return Response::json([
            'students' => $students->toArray(),
            'count' => $students->count(),
            'has_more' => $hasMore,
            'next_cursor_id' => $nextCursorId,
            'query_meta' => [
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'study_year' => is_numeric($studyYear) ? (int) $studyYear : null,
                'cursor_enabled' => $cursorEnabled,
            ],
        ]);
    }

    /**
     * Define the input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'q' => $schema->string()->description('Optional search term (name or student_id). Leave empty to list all students.'),
            'department' => $schema->string()->description('Optional exact department filter (as stored in DB).'),
            'study_year' => $schema->integer()->description('Academic year level filter (1-5). This is not a calendar year.'),
            'min_cgpa' => $schema->number()->description('Minimum CGPA filter.'),
            'sort_by' => $schema->string()->description('Sort field: id, cgpa, current_year, or name. Use sort_by=cgpa for score ranking.'),
            'sort_order' => $schema->string()->description('Sort direction: asc or desc. Use desc for top scores.'),
            'limit' => $schema->integer()->description('Rows to return (1-100). For "list 50 students", set limit=50 and omit q.'),
            'cursor_id' => $schema->integer()->description('Pagination cursor (supported when sort_by=id). Return rows with id > cursor_id until next_cursor_id is null.'),
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
