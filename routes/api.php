<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');

// projects routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/projects', [ProjectsController::class, 'index']);
    Route::get('/projects/{projects}', [ProjectsController::class, 'show']);
    Route::post('/projects', [ProjectsController::class, 'store']);
    Route::put('/projects/{projects}', [ProjectsController::class, 'update']);
    Route::delete('/projects/{projects}', [ProjectsController::class, 'destroy']);
});
