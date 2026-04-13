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
#[Description('List/search departments with optional pagination to avoid large payloads.')]
class DepartmentList extends Tool
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
        if (! $user) {
            return Response::error('Unauthenticated');
        }

        $q = trim((string) $this->param($request, 'q', ''));
        $cursorId = $this->param($request, 'cursor_id');
        $limit = (int) $this->param($request, 'limit', 25);
        $limit = max(1, min(self::MAX_LIMIT, $limit));

        $query = Department::query()
            ->select(['id', 'name', 'code', 'min_gpa', 'spot_limit'])
            ->orderBy('id', 'asc');

        if ($cursorId !== null && is_numeric($cursorId)) {
            $query->where('id', '>', (int) $cursorId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('code', 'like', '%'.$q.'%');
            });
        }

        $rows = $query->limit($limit + 1)->get();
        $hasMore = $rows->count() > $limit;
        $departments = $rows->take($limit)->values();

        $nextCursorId = null;
        if ($hasMore && $departments->isNotEmpty()) {
            $nextCursorId = $departments->last()->id ?? null;
        }

        return Response::json([
            'departments' => $departments->toArray(),
            'count' => $departments->count(),
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
                'q' => ['type' => 'string', 'description' => 'Search by department name or code.'],
                'limit' => ['type' => 'integer', 'description' => 'Max rows to return (1-25). Defaults to 25.'],
                'cursor_id' => ['type' => 'integer', 'description' => 'Return rows with id > cursor_id (pagination).'],
            ],
            'additionalProperties' => false,
        ];
    }
}