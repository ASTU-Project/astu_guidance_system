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
#[Description('List all departments with their details (id, name, code, min_gpa, spot_limit, timestamps). No input parameters.')]
class DepartmentList extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $user = $request->user();
        if (!$user) {
            return Response::error('Unauthenticated');
        }

        $departments = Department::query()
            ->select(['id', 'name', 'code', 'min_gpa', 'spot_limit', 'created_at', 'updated_at'])
            ->orderBy('name', 'asc')
            ->get();

        return Response::json([
            'departments' => $departments->toArray(),
            'count' => $departments->count(),
        ]);
    }

    /**
     * Define the input schema – no parameters needed.
     */
    public function schema(JsonSchema $schema): array
    {
        return []; // Empty = no parameters
    }
}