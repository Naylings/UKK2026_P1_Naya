<?php

use App\Http\Controllers\AppConfig\AppConfigController;
use App\Http\Controllers\ActivityLog\ActivityLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\UserDashboardController;
use App\Http\Controllers\Loan\LoanController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Return\ReturnController;
use App\Http\Controllers\Settlement\SettlementController;
use App\Http\Controllers\Tool\ToolController;
use App\Http\Controllers\ToolUnit\ToolUnitController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Violation\ViolationController;
use App\Http\Controllers\Appeal\AppealController;
use Illuminate\Support\Facades\Route;



Route::prefix('auth')->group(function () {

    
    Route::post('login', [AuthController::class, 'login']);

    
    Route::middleware('auth:api')->group(function () {
        Route::post('logout',  [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me',       [AuthController::class, 'me']);
    });
});



Route::prefix('users')->middleware('auth:api')->group(function () {

    
    Route::get('/',                 [UserController::class, 'index']);
    Route::post('/',                [UserController::class, 'store']);

    
    Route::get('/{user}',           [UserController::class, 'show']);
    Route::put('/{user}',           [UserController::class, 'update']);
    Route::delete('/{user}',        [UserController::class, 'destroy']);

    
    Route::post('/{user}/credit',   [UserController::class, 'updateCredit']);
});




Route::prefix('categories')->middleware('auth:api')->group(function () {

    
    Route::get('/',                 [CategoryController::class, 'index']);
    Route::post('/',                [CategoryController::class, 'store']);

    
    Route::put('/{category}',           [CategoryController::class, 'update']);
    Route::delete('/{category}',        [CategoryController::class, 'destroy']);
});



Route::prefix('tools')->middleware('auth:api')->group(function () {

    
    Route::get('/',                 [ToolController::class, 'index']);
    Route::post('/',                [ToolController::class, 'store']);

    
    Route::get('/{tool}',           [ToolController::class, 'show']);
    Route::put('/{tool}',           [ToolController::class, 'update']);
    Route::delete('/{tool}',        [ToolController::class, 'destroy']);
});



Route::prefix('tool-units')->middleware('auth:api')->group(function () {

    
    Route::get('/',                         [ToolUnitController::class, 'index']);
    Route::post('/',                        [ToolUnitController::class, 'store']);

    
    Route::get('/available',                [ToolUnitController::class, 'availableUnits']);

    
    Route::get('/{code}',                   [ToolUnitController::class, 'show']);
    Route::put('/{code}',                   [ToolUnitController::class, 'update']);
    Route::delete('/{code}',                [ToolUnitController::class, 'destroy']);

    
    Route::post('/{code}/record-condition', [ToolUnitController::class, 'recordCondition']);
    Route::get('/{code}/history',           [ToolUnitController::class, 'conditionHistory']);
});


Route::prefix('app-config')->group(function () {
    Route::get('/', [AppConfigController::class, 'show']);
    Route::middleware('auth:api')->group(function () {
        Route::put('/', [AppConfigController::class, 'update']);
    });
});

Route::prefix('loans')->middleware('auth:api')->group(function () {
    Route::post('/', [LoanController::class, 'store']);
    Route::get('/', [LoanController::class, 'index']);
    Route::get('/my', [LoanController::class, 'userLoans']);


    Route::post('/{loanId}/approve', [LoanController::class, 'approve']);
    Route::post('/{loanId}/reject', [LoanController::class, 'reject']);
});




Route::prefix('returns')->middleware('auth:api')->group(function () {

    
    Route::post('/{loanId}', [ReturnController::class, 'store']);

    
    Route::post('/{loanId}/confirm', [ReturnController::class, 'confirm']);

    
    Route::get('/', [ReturnController::class, 'index']);

    
    Route::get('/{id}', [ReturnController::class, 'show']);
});







Route::middleware('auth:api')->post('/violations/{violationId}/settle', [SettlementController::class, 'store']);

Route::prefix('violations')->middleware('auth:api')->group(function () {
    Route::get('/', [ViolationController::class, 'index']);
    Route::get('/{id}', [ViolationController::class, 'show']);
});

Route::prefix('settlements')->middleware('auth:api')->group(function () {
    Route::get('/', [SettlementController::class, 'index']);
    Route::get('/{id}', [SettlementController::class, 'show']);
});
Route::prefix('activity-logs')->middleware('auth:api')->group(function () {
    Route::get('/', [ActivityLogController::class, 'index']);
    Route::get('/{id}', [ActivityLogController::class, 'show']);
});



Route::prefix('reports')->middleware('auth:api')->group(function () {
    Route::get('/{type}/preview', [ReportController::class, 'preview']);
    Route::get('/{type}/export', [ReportController::class, 'export']);
});




Route::prefix('appeals')->middleware('auth:api')->group(function () {
    Route::post('/', [AppealController::class, 'store']);
    Route::get('/', [AppealController::class, 'index']);
    Route::get('/my', [AppealController::class, 'myAppeals']);
});

Route::prefix('appeals')->middleware('auth:api')->group(function () {
    Route::patch('/{id}/review', [AppealController::class, 'review']);
});

Route::prefix('dashboard')->middleware('auth:api')->group(function () {
    Route::get('/user', [UserDashboardController::class, 'index']);
    Route::get('/admin', [AdminDashboardController::class, 'index']);
});

