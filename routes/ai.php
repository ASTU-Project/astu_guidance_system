<?php
use Laravel\Mcp\Facades\Mcp;
use App\Mcp\Servers\DepartmentServer;

Mcp::web('/mcp/departments', DepartmentServer::class);