<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\SourceAccountController;
use App\Http\Controllers\Api\V1\TaskCommentController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // Public auth
    Route::post('/auth/google', [AuthController::class, 'google']);

    // Authenticated
    Route::middleware(['auth:sanctum', 'allowed'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

        Route::apiResource('tasks', TaskController::class);
        Route::post('tasks/{task}/complete', [TaskController::class, 'complete']);
        Route::post('tasks/{task}/ignore', [TaskController::class, 'ignore']);
        Route::post('tasks/{task}/reopen', [TaskController::class, 'reopen']);
        Route::post('tasks/{task}/reanalyze', [TaskController::class, 'reanalyze']);
        Route::post('tasks/{task}/push-to-monday', [TaskController::class, 'pushToMonday']);
        Route::post('tasks/{task}/push-to-wrike', [TaskController::class, 'pushToWrike']);

        Route::apiResource('tasks.comments', TaskCommentController::class)
            ->shallow()->parameters(['comments' => 'comment']);

        Route::apiResource('source-accounts', SourceAccountController::class);
        Route::post('source-accounts/{source_account}/sync', [SourceAccountController::class, 'sync']);
        Route::post('source-accounts/{source_account}/enable', [SourceAccountController::class, 'enable']);
        Route::post('source-accounts/{source_account}/disable', [SourceAccountController::class, 'disable']);
    });
});
