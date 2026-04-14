<?php

use App\Http\Controllers\AppConfig\AppConfigController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Tool\ToolController;
use App\Http\Controllers\ToolUnit\ToolUnitController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Loan\LoanController;
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

/*
|--------------------------------------------------------------------------
| Tool CRUD Routes
|--------------------------------------------------------------------------
|
| prefix  : /api/tools
| middleware: auth:api (JWT protected)
|
*/

Route::prefix('tools')->middleware('auth:api')->group(function () {

    // List & Create
    Route::get('/',                 [ToolController::class, 'index']);
    Route::post('/',                [ToolController::class, 'store']);

    // Show, Update, Delete
    Route::get('/{tool}',           [ToolController::class, 'show']);
    Route::put('/{tool}',           [ToolController::class, 'update']);
    Route::delete('/{tool}',        [ToolController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Tool Unit CRUD Routes
|--------------------------------------------------------------------------
|
| prefix  : /api/tool-units
| middleware: auth:api (JWT protected)
|
*/

Route::prefix('tool-units')->middleware('auth:api')->group(function () {

    // List & Create
    Route::get('/',                         [ToolUnitController::class, 'index']);
    Route::post('/',                        [ToolUnitController::class, 'store']);

    // Available units for loan (peminjam access)
    Route::get('/available',                [ToolUnitController::class, 'availableUnits']);

    // Show, Update, Delete
    Route::get('/{code}',                   [ToolUnitController::class, 'show']);
    Route::put('/{code}',                   [ToolUnitController::class, 'update']);
    Route::delete('/{code}',                [ToolUnitController::class, 'destroy']);

    // Record condition & history
    Route::post('/{code}/record-condition', [ToolUnitController::class, 'recordCondition']);
    Route::get('/{code}/history',           [ToolUnitController::class, 'conditionHistory']);
});

/*
|--------------------------------------------------------------------------
| App Config Routes
|--------------------------------------------------------------------------
|
| prefix  : /api/app-config
| middleware: auth:api (JWT protected)
|
*/
Route::prefix('app-config')->group(function () {
    Route::get('/', [AppConfigController::class, 'show']);
    Route::middleware('auth:api')->group(function () {
        Route::put('/', [AppConfigController::class, 'update']);
    });

});
/*
|--------------------------------------------------------------------------
| Loans Routes
|--------------------------------------------------------------------------
|
| prefix  : /api/loans
| middleware: auth:api (JWT protected)
|
*/
Route::prefix('loans')->group(function () {
    Route::post('/', [LoanController::class, 'store']);
    Route::get('/', [LoanController::class, 'index']);
    Route::get('/my', [LoanController::class, 'userLoans']);
});