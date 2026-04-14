<?php
use Laravel\Mcp\Facades\Mcp;
use App\Mcp\Servers\SystemServer;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function (): void {
	Mcp::web('/mcp/system', SystemServer::class);
});