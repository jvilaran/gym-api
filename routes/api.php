<?php

use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\ExerciseTypeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
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
        Route::get('/exercise-types/{id}', 'show');
        Route::put('/exercise-types/{id}', 'update');
        Route::delete('/exercise-types/{id}', 'destroy');
    });

    Route::controller(ExerciseController::class)->group(function () {
        Route::get('/exercises', 'index');
        Route::post('/exercises', 'store');
        Route::get('/exercises/{id}', 'show');
        Route::put('/exercises/{id}', 'update');
        Route::delete('/exercises/{id}', 'destroy');
    });
});
