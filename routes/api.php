<?php

use App\Http\Controllers\ArtifactsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DomainController;
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

//artifacts routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/artifacts', [ArtifactsController::class, 'index']);
    Route::get('/artifacts/{artifacts}', [ArtifactsController::class, 'show']);
    Route::post('/artifacts', [ArtifactsController::class, 'store']);
    Route::put('/artifacts/{artifacts}', [ArtifactsController::class, 'update']);
    Route::delete('/artifacts/{artifacts}', [ArtifactsController::class, 'destroy']);
});

//domains routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/domains', [DomainController::class, 'index']);
    Route::get('/domains/{domain}', [DomainController::class, 'show']);
    Route::post('/domains', [DomainController::class, 'store']);
    Route::put('/domains/{domain}', [DomainController::class, 'update']);
    Route::delete('/domains/{domain}', [DomainController::class, 'destroy']);
});
