<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\DepartmentSummaryPrompt;
use App\Mcp\Prompts\MapNavigationPrompt;
use App\Mcp\Tools\DepartmentList;
use App\Mcp\Tools\MapLocationList;
use App\Mcp\Tools\PolicyList;
use App\Mcp\Tools\StudentList;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('System Server')]
#[Version('1.1.0')]
#[Instructions('Manage university departments, students, and policies using grounded DB tools only. Method rules: (1) list-all: omit q/question and set limit directly from user ask; (2) detail: use id when user asks specific record; (3) ranking/sorting: set sort_by and sort_order explicitly; (4) pagination: use cursor_id only when sort_by=id and continue until has_more=false or next_cursor_id is null; (5) never guess search terms such as names/codes if the user asked for a plain list.')]
class SystemServer extends Server
{
    protected array $tools = [
        DepartmentList::class,
        StudentList::class,
        PolicyList::class,
        MapLocationList::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        DepartmentSummaryPrompt::class,
        MapNavigationPrompt::class,
    ];
}
