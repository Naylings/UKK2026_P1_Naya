<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
|
| prefix  : /api/auth
| middleware: api (default Laravel)
|
*/

Route::prefix('auth')->group(function () {

    // ── Public ──────────────────────────────────────────────────────────────
    Route::post('login', [AuthController::class, 'login']);

    // ── Protected (butuh JWT yang valid) ────────────────────────────────────
    Route::middleware('auth:api')->group(function () {
        Route::post('logout',  [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me',       [AuthController::class, 'me']);
    });

});
