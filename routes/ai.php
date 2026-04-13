<?php
use Laravel\Mcp\Facades\Mcp;
use App\Mcp\Servers\SystemServer;

Mcp::web('/mcp/system', SystemServer::class);