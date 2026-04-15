<?php

namespace App\Mcp\Tools;

use App\Models\Policy;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('policy.list')]
#[Description('List, search, and fetch policy details. For list-all requests, omit q and question. For specific details, provide id. For relevance matching from a user question, provide question. Use sort_by + sort_order for deterministic ordering; cursor_id pagination is supported when sort_by=id.')]
class PolicyList extends Tool
{
    private const MAX_LIMIT = 100;
    private const ALLOWED_SORT_BY = ['id', 'updated_at', 'title', 'category'];
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

    public function handle(Request $request): Response
    {
        $user = $request->get('user') ?? $request->user();

        if (! $user instanceof User && ! $user instanceof Student) {
            return Response::error('Unauthenticated');
        }

        $isAdminUser = $user instanceof User && $this->isAdmin($user);
        $isStudentUser = $user instanceof Student;

        $q = trim((string) $this->param($request, 'q', ''));
        $id = $this->param($request, 'id');
        $question = trim((string) $this->param($request, 'question', ''));
        $category = trim((string) $this->param($request, 'category', ''));
        $activeOnly = (bool) $this->param($request, 'active_only', true);
        $cursorId = $this->param($request, 'cursor_id');
        $sortBy = strtolower(trim((string) $this->param($request, 'sort_by', 'id')));
        $sortOrder = strtolower(trim((string) $this->param($request, 'sort_order', 'asc')));
        $limit = (int) $this->param($request, 'limit', 10);
        $limit = max(1, min(self::MAX_LIMIT, $limit));

        // Students can only read active policies.
        if ($isStudentUser && ! $isAdminUser) {
            $activeOnly = true;
        }

        if (! in_array($sortBy, self::ALLOWED_SORT_BY, true)) {
            $sortBy = 'id';
        }

        if (! in_array($sortOrder, self::ALLOWED_SORT_ORDER, true)) {
            $sortOrder = 'asc';
        }

        $cursorEnabled = $sortBy === 'id';

        $query = Policy::query()
            ->select(['id', 'title', 'content', 'category', 'is_active', 'updated_at'])
            ->orderBy('id', 'asc');

        if ($id !== null && is_numeric($id)) {
            $query->where('id', (int) $id);
        }

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        if ($cursorEnabled && $cursorId !== null && is_numeric($cursorId)) {
            $query->where('id', '>', (int) $cursorId);
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', '%'.$q.'%')
                    ->orWhere('category', 'like', '%'.$q.'%')
                    ->orWhere('content', 'like', '%'.$q.'%');
            });
        }

        $query->orderBy($sortBy, $sortOrder)->orderBy('id', 'asc');

        $rows = null;
        $keywords = $this->extractQuestionKeywords($question);
        if ($keywords !== []) {
            $keywordQuery = clone $query;
            $keywordQuery->where(function ($sub) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $sub->orWhere('title', 'like', '%'.$keyword.'%')
                        ->orWhere('category', 'like', '%'.$keyword.'%')
                        ->orWhere('content', 'like', '%'.$keyword.'%');
                }
            });

            $rows = $keywordQuery->limit($cursorEnabled ? ($limit + 1) : $limit)->get();
        }

        // If keyword filtering becomes too strict, fall back to the broader query.
        if ($rows === null || $rows->isEmpty()) {
            $rows = $query->limit($cursorEnabled ? ($limit + 1) : $limit)->get();
        }

        $hasMore = $cursorEnabled ? ($rows->count() > $limit) : false;

        $policies = $rows->take($limit)->map(function (Policy $policy): array {
            return [
                'id' => $policy->id,
                'title' => $policy->title,
                'category' => $policy->category,
                'content' => $policy->content,
                'is_active' => (bool) $policy->is_active,
                'updated_at' => optional($policy->updated_at)->toDateTimeString(),
            ];
        })->values();

        $nextCursorId = null;
        if ($cursorEnabled && $hasMore && $policies->isNotEmpty()) {
            $nextCursorId = $policies->last()['id'] ?? null;
        }

        return Response::json([
            'policies' => $policies->toArray(),
            'count' => $policies->count(),
            'has_more' => $hasMore,
            'next_cursor_id' => $nextCursorId,
            'query_meta' => [
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'cursor_enabled' => $cursorEnabled,
                'question_mode' => $question !== '' ? 'relevance' : 'direct',
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'q' => $schema->string()->description('Search by title, category, or content.'),
            'id' => $schema->integer()->description('Fetch a specific policy by its id.'),
            'question' => $schema->string()->description('Original student-related question to find relevant policies by keywords.'),
            'category' => $schema->string()->description('Exact category filter.'),
            'active_only' => $schema->boolean()->description('Return only active policies (default true).'),
            'sort_by' => $schema->string()->description('Sort field: id, updated_at, title, or category.'),
            'sort_order' => $schema->string()->description('Sort direction: asc or desc.'),
            'limit' => $schema->integer()->description('Rows to return (1-100).'),
            'cursor_id' => $schema->integer()->description('Pagination cursor (supported when sort_by=id). Return rows with id > cursor_id.'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function extractQuestionKeywords(string $question): array
    {
        if ($question === '') {
            return [];
        }

        $parts = preg_split('/[^a-zA-Z0-9]+/', strtolower($question)) ?: [];
        $stopWords = [
            'the', 'and', 'for', 'with', 'from', 'that', 'this', 'what', 'when', 'where', 'which',
            'about', 'into', 'your', 'their', 'they', 'them', 'have', 'has', 'had', 'will', 'would',
            'should', 'could', 'can', 'student', 'students', 'policy', 'policies', 'rule', 'rules',
            'regulation', 'regulations', 'give', 'show', 'display', 'list', 'all', 'please', 'me',
            'get', 'provide', 'tell', 'want',
            'university', 'college', 'institution', 'astu',
        ];

        $filtered = [];
        foreach ($parts as $part) {
            if ($part === '' || strlen($part) < 3 || in_array($part, $stopWords, true)) {
                continue;
            }

            $filtered[] = $part;
            if (count($filtered) >= 8) {
                break;
            }
        }

        return array_values(array_unique($filtered));
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

        return (int) $user->id === 1;
    }
}
