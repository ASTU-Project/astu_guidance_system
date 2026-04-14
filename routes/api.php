<?php

use App\Http\Controllers\Auth\ApiTokenController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/token', [ApiTokenController::class, 'store'])->name('api.auth.token');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/auth/me', [ApiTokenController::class, 'me'])->name('api.auth.me');
    Route::delete('/auth/token', [ApiTokenController::class, 'destroy'])->name('api.auth.token.destroy');
});
