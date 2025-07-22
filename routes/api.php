<?php

use App\Http\Controllers\APIV1\AuthController;
use App\Http\Controllers\APIV1\ProfileController;
use App\Http\Controllers\APIV1\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/v1')->group(function () {
    Route::post('/register', [AuthController::class, 'registration']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        //auth route
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('logout', [AuthController::class, 'logout']);

        // task route 
        Route::apiResource('/tasks', TaskController::class);
        Route::post('/tasks/{task}/restore', [TaskController::class, 'restore']);
        Route::delete('/tasks/{task}/force-delete', [TaskController::class, 'forceDelete']);
        Route::post('tasks/filter', [TaskController::class, 'filter']);
        Route::post('/tasks/{task}/update-status', [TaskController::class, 'updateStatus']);

        // profile route

        Route::put('/update-profile', [ProfileController::class, 'update']);
    });
});
