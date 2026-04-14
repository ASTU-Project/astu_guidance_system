<?php

namespace App\Mcp\Tools;

use App\Models\Department;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('department.list')]
#[Description('List, search, and sort departments. For list-all requests, omit q and set limit directly. For deterministic ranking, use sort_by + sort_order. cursor_id pagination is supported when sort_by=id.')]
class DepartmentList extends Tool
{
    private const MAX_LIMIT = 100;
    private const ALLOWED_SORT_BY = ['id', 'name', 'code', 'min_gpa', 'spot_limit'];
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
        if (! $user) {
            return Response::error('Unauthenticated');
        }

        $q = trim((string) $this->param($request, 'q', ''));
        $cursorId = $this->param($request, 'cursor_id');
        $sortBy = strtolower(trim((string) $this->param($request, 'sort_by', 'id')));
        $sortOrder = strtolower(trim((string) $this->param($request, 'sort_order', 'asc')));
        $limit = (int) $this->param($request, 'limit', 25);
        $limit = max(1, min(self::MAX_LIMIT, $limit));

        if (! in_array($sortBy, self::ALLOWED_SORT_BY, true)) {
            $sortBy = 'id';
        }

        if (! in_array($sortOrder, self::ALLOWED_SORT_ORDER, true)) {
            $sortOrder = 'asc';
        }

        $cursorEnabled = $sortBy === 'id';

        $query = Department::query()
            ->select(['id', 'name', 'code', 'min_gpa', 'spot_limit']);

        if ($cursorEnabled && $cursorId !== null && is_numeric($cursorId)) {
            $query->where('id', '>', (int) $cursorId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('code', 'like', '%'.$q.'%');
            });
        }

        $query->orderBy($sortBy, $sortOrder)->orderBy('id', 'asc');

        $rows = $query->limit($cursorEnabled ? ($limit + 1) : $limit)->get();
        $hasMore = $cursorEnabled ? ($rows->count() > $limit) : false;
        $departments = $rows->take($limit)->values();

        $nextCursorId = null;
        if ($cursorEnabled && $hasMore && $departments->isNotEmpty()) {
            $nextCursorId = $departments->last()->id ?? null;
        }

        return Response::json([
            'departments' => $departments->toArray(),
            'count' => $departments->count(),
            'has_more' => $hasMore,
            'next_cursor_id' => $nextCursorId,
            'query_meta' => [
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
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
            'q' => $schema->string()->description('Optional search term by department name or code. Leave empty to list all departments.'),
            'sort_by' => $schema->string()->description('Sort field: id, name, code, min_gpa, or spot_limit.'),
            'sort_order' => $schema->string()->description('Sort direction: asc or desc.'),
            'limit' => $schema->integer()->description('Rows to return (1-100). For "list all departments", set limit and omit q.'),
            'cursor_id' => $schema->integer()->description('Pagination cursor (supported when sort_by=id). Return rows with id > cursor_id.'),
        ];
    }
}