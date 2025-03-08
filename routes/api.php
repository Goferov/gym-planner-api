<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/getUser', [AuthController::class, 'getUser'])
    ->middleware(['auth:api']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware(['auth:api']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
