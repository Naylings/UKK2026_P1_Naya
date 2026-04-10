<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\User\UserController;
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
    Route::get('/',                 [UserController::class, 'index']);
    Route::post('/',                [UserController::class, 'store']);

    // Show, Update, Delete
    Route::get('/{user}',           [UserController::class, 'show']);
    Route::put('/{user}',           [UserController::class, 'update']);
    Route::delete('/{user}',        [UserController::class, 'destroy']);

    // Update credit only
    Route::post('/{user}/credit',   [UserController::class, 'updateCredit']);
});


/*
|--------------------------------------------------------------------------
| Category CRUD Routes
|--------------------------------------------------------------------------
|
| prefix  : /api/categories
| middleware: auth:api (JWT protected)
|
*/

Route::prefix('categories')->middleware('auth:api')->group(function () {

    // List & Create
    Route::get('/',                 [CategoryController::class, 'index']);
    Route::post('/',                [CategoryController::class, 'store']);

    //  Update, Delete
    Route::put('/{category}',           [CategoryController::class, 'update']);
    Route::delete('/{category}',        [CategoryController::class, 'destroy']);
});
