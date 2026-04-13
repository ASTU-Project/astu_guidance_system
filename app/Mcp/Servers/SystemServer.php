<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\DepartmentSummaryPrompt;
use App\Mcp\Tools\DepartmentList;
use App\Mcp\Tools\StudentList;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('System Server')]
#[Version('1.1.0')]
#[Instructions('Manage university departments and students: list data tools')]
class SystemServer extends Server
{
    protected array $tools = [
        DepartmentList::class,
        StudentList::class,
        // DepartmentCreate::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        DepartmentSummaryPrompt::class,
    ];
}
