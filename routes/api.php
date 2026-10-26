<?php

use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\ExerciseTypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/users', 'index');
        Route::post('/users', 'store');
        Route::get('/users/{id}', 'show');
        Route::put('/users/{id}', 'update');
        Route::delete('/users/{id}', 'destroy');
    });

    Route::controller(ExerciseTypeController::class)->group(function () {
        Route::get('/exercise-types', 'index');
        Route::post('/exercise-types', 'store');
        Route::post('/exercise-types/from-catalog', 'fromCatalog');
        Route::get('/exercise-types/{id}', 'show');
        Route::put('/exercise-types/{id}', 'update');
        Route::delete('/exercise-types/{id}', 'destroy');
    });

    Route::controller(CatalogController::class)->group(function () {
        Route::get('/catalog/muscles', 'muscles');
        Route::get('/catalog/muscles/{muscle}', 'byMuscle');
        Route::get('/catalog/exercises', 'exercises');
        Route::get('/catalog/exercises/{muscle}/{slug}', 'show');
    });

    Route::controller(ExerciseController::class)->group(function () {
        Route::get('/exercises', 'index');
        Route::post('/exercises', 'store');
        Route::get('/exercises/{id}', 'show');
        Route::put('/exercises/{id}', 'update');
        Route::delete('/exercises/{id}', 'destroy');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard/summary', 'summary');
    });

    Route::controller(AiController::class)->group(function () {
        Route::get('/ai/recommendation', 'recommendation');
        Route::get('/ai/analysis', 'analysis');
    });
});
