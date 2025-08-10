<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AccountController,
    AuthController,
    ClientController,
    DashboardController,
    ExerciseController,
    ExerciseLogController,
    PlanController,
    PlanUserController
};

// Public Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Authenticated routes
Route::middleware(['auth:api'])->group(function () {

    // Auth Info
    Route::get('/getUser', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Account
    Route::prefix('me')->group(function () {
        Route::put('/profile',  [AccountController::class, 'updateProfile']);
        Route::put('/password', [AccountController::class, 'updatePassword']);
    });

    // Dashboard
    Route::prefix('dashboard')->middleware('throttle:api')->group(function () {
        Route::get('/metrics',             [DashboardController::class, 'metrics']);
        Route::get('/performance',         [DashboardController::class, 'performance']);
        Route::get('/recent-clients',      [DashboardController::class, 'recentClients']);
        Route::get('/activity',            [DashboardController::class, 'activity']);
        Route::get('/users/{client}/metrics',      [DashboardController::class, 'userMetrics']);
        Route::get('/users/{client}/performance',  [DashboardController::class, 'userPerformance']);
    });

    // Plan User
    Route::prefix('plan-user')->group(function () {
        Route::get('/',                          [PlanUserController::class, 'index']);
        Route::post('{planUser}/start',         [PlanUserController::class, 'start']);
        Route::get('{planUser}/history',        [PlanUserController::class, 'history']);
        Route::get('{planUser}',                [PlanUserController::class, 'show']);
        Route::get('{planUser}/day',            [PlanUserController::class, 'showDay']);
        Route::post('{planUser}/day/start',     [PlanUserController::class, 'startDay']);
        Route::get('{planUser}/day/summary',    [PlanUserController::class, 'summary']);
    });

    // Exercises
    Route::middleware('throttle:api')->group(function () {
        Route::apiResource('exercises', ExerciseController::class);
        Route::get('/muscle-groups', [ExerciseController::class, 'muscleGroups']);
    });

    // Clients
    Route::apiResource('clients', ClientController::class)->middleware('throttle:api');

    // Plans
    Route::apiResource('plans', PlanController::class)->middleware('throttle:api');
    Route::post('/plans/{plan}/assign', [PlanController::class, 'assignPlan']);
    Route::delete('/plans/{plan}/unassign', [PlanController::class, 'unassignPlan']);

    // Exercise Logs
    Route::prefix('exercise-logs')->group(function () {
        Route::post('{exerciseLog}/mark-complete',      [ExerciseLogController::class, 'markComplete']);
        Route::post('{exerciseLog}/report-difficulty',  [ExerciseLogController::class, 'reportDifficulty']);
    });
});


// routes/api.php – fragment
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('plans', PlanController::class)->only('index');
    Route::post('/plans/{plan}/assign', [PlanController::class, 'assignPlan']);

    Route::prefix('plan-user')->group(function () {
        Route::post('{planUser}/start',      [PlanUserController::class, 'start']);
        Route::get('{planUser}/day',         [PlanUserController::class, 'showDay']);
        Route::post('{planUser}/day/start',  [PlanUserController::class, 'startDay']);
    });
});
