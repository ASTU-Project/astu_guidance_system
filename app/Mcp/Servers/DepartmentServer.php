<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\DepartmentSummaryPrompt;
use App\Mcp\Tools\DepartmentList;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Department Server')]
#[Version('1.0.0')]
#[Instructions('Manage university departments: list (public)')]
class DepartmentServer extends Server
{
    protected array $tools = [
        DepartmentList::class,
        // DepartmentCreate::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        DepartmentSummaryPrompt::class,
    ];
}
