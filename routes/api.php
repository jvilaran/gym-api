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
});
/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */

Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'index');
    Route::post('/users', 'store');
    Route::get('/users/{user}', 'show');
    Route::put('/users/{user}', 'update');
    Route::delete('/users/{user}', 'destroy');
});

Route::controller(ExerciseTypeController::class)->group(function () {
    Route::get('/exercise-types', 'index');
    Route::post('/exercise-types', 'store');
    Route::get('/exercise-types/{exerciseType}', 'show');
    Route::put('/exercise-types/{exerciseType}', 'update');
    Route::delete('/exercise-types/{exerciseType}', 'destroy');
});

Route::controller(ExerciseController::class)->group(function () {
    Route::get('/exercises', 'index');
    Route::post('/exercises', 'store');
    Route::get('/exercises/{exercise}', 'show');
    Route::put('/exercises/{exercise}', 'update');
    Route::delete('/exercises/{exercise}', 'destroy');
});