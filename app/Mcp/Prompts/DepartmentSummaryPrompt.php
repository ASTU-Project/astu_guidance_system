<?php

namespace App\Mcp\Prompts;

use App\Models\Department;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Description('Summarize departments and suggest next admin actions.')]
class DepartmentSummaryPrompt extends Prompt
{
    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): Response
    {
        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'spot_limit', 'min_gpa']);

        $summary = [
            'title' => 'Department Summary',
            'count' => $departments->count(),
            'departments' => $departments->toArray(),
            'next_actions' => [
                'Review department capacity and remaining spots.',
                'Verify minimum GPA rules for each department.',
            ],
        ];

        return Response::json($summary);
    }

    /**
     * Get the prompt's arguments.
     *
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [];
    }
}
