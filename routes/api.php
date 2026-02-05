<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::apiResource('tasks', TaskController::class);

Route::prefix('tasks')->group(function () {
    Route::post('/{task}/complete', [TaskController::class, 'complete']);
    Route::post('/{task}/restore',  [TaskController::class, 'restore']);
    Route::get('/statistics',       [TaskController::class, 'statistics']);
});
