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

/*
|--------------------------------------------------------------------------
| User CRUD Routes
|--------------------------------------------------------------------------
|
| prefix  : /api/users
| middleware: auth:api (JWT protected)
|
*/

Route::prefix('users')->middleware('auth:api')->group(function () {
    
    // List & Create
    Route::get('/',                 [AuthController::class, 'indexUsers']);
    Route::post('/',                [AuthController::class, 'storeUser']);
    
    // Show, Update, Delete
    Route::get('/{user}',           [AuthController::class, 'showUser']);
    Route::put('/{user}',           [AuthController::class, 'updateUser']);
    Route::delete('/{user}',        [AuthController::class, 'destroyUser']);
    
    // Update credit only
    Route::post('/{user}/credit',   [AuthController::class, 'updateUserCredit']);
    
});

