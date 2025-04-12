<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\ExerciseLogController;
use App\Http\Controllers\Api\PlanController;
use Illuminate\Support\Facades\Route;


// Auth
Route::get('/getUser', [AuthController::class, 'getUser'])
    ->middleware(['auth:api']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware(['auth:api']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


// Exercises
Route::apiResource('exercises', ExerciseController::class)
    ->middleware(['auth:api', 'throttle:api']);

Route::get('/muscle-groups', [ExerciseController::class, 'muscleGroups'])
    ->middleware(['auth:api', 'throttle:api']);


// Clients
Route::apiResource('clients', ClientController::class)
    ->middleware(['auth:api', 'throttle:api']);

// Plans
Route::apiResource('plans', PlanController::class)
    ->middleware(['auth:api', 'throttle:api']);

Route::post('/plans/{plan}/assign', [PlanController::class, 'assignPlan'])
    ->middleware(['auth:api']);

Route::delete('/plans/{plan}/unassign', [PlanController::class, 'unassignPlan'])
    ->middleware(['auth:api']);

// Exercise Logs
Route::post('/exercise-logs/{exerciseLog}/mark-complete', [ExerciseLogController::class, 'markComplete'])
    ->middleware(['auth:api']);

Route::post('/exercise-logs/{exerciseLog}/report-difficulty', [ExerciseLogController::class, 'reportDifficulty'])
    ->middleware(['auth:api']);
