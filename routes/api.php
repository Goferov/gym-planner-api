<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExerciseController;
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
