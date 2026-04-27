<?php

namespace App\Mcp\Tools;

use App\Models\MapLocation;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('map.location_list')]
#[Description('List and search campus map locations. You can use the returned latitude and longitude to show a Google Maps link format like [Open Map](https://maps.google.com/?q={lat},{lon}). For listing, omit q and set limit. cursor_id pagination is supported when sort_by=id.')]
class MapLocationList extends Tool
{
    private const MAX_LIMIT = 100;
    private const ALLOWED_SORT_BY = ['id', 'name', 'category'];
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
        if (!$user) {
            return Response::error('Unauthenticated');
        }

        $q = trim((string) $this->param($request, 'q', ''));
        $category = trim((string) $this->param($request, 'category', ''));
        $cursorId = $this->param($request, 'cursor_id');
        $sortBy = strtolower(trim((string) $this->param($request, 'sort_by', 'name')));
        $sortOrder = strtolower(trim((string) $this->param($request, 'sort_order', 'asc')));
        $limit = (int) $this->param($request, 'limit', 25);
        $limit = max(1, min(self::MAX_LIMIT, $limit));

        if (!in_array($sortBy, self::ALLOWED_SORT_BY, true)) {
            $sortBy = 'name';
        }

        if (!in_array($sortOrder, self::ALLOWED_SORT_ORDER, true)) {
            $sortOrder = 'asc';
        }

        $cursorEnabled = $sortBy === 'id';

        $query = MapLocation::query()
            ->select(['id', 'name', 'description', 'latitude', 'longitude', 'category', 'icon', 'image_url']);

        if ($cursorEnabled && $cursorId !== null && is_numeric($cursorId)) {
            $query->where('id', '>', (int) $cursorId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('category', 'like', '%' . $q . '%');
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        $query->orderBy($sortBy, $sortOrder)->orderBy('id', 'asc');

        $rows = $query->limit($cursorEnabled ? ($limit + 1) : $limit)->get();
        $hasMore = $cursorEnabled ? ($rows->count() > $limit) : false;
        $locations = $rows->take($limit)->values();

        $nextCursorId = null;
        if ($cursorEnabled && $hasMore && $locations->isNotEmpty()) {
            $nextCursorId = $locations->last()->id ?? null;
        }

        return Response::json([
            'locations' => $locations->toArray(),
            'count' => $locations->count(),
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
            'q' => $schema->string()->description('Optional search term by location name, description, or category.'),
            'category' => $schema->string()->description('Exact category filter (e.g., "Building", "Facility", "Office").'),
            'sort_by' => $schema->string()->description('Sort field: id, name, or category.'),
            'sort_order' => $schema->string()->description('Sort direction: asc or desc.'),
            'limit' => $schema->integer()->description('Rows to return (1-100). For "list all locations", set limit and omit q.'),
            'cursor_id' => $schema->integer()->description('Pagination cursor (supported when sort_by=id). Return rows with id > cursor_id.'),
        ];
    }
}
