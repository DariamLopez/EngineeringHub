<?php

use App\Http\Controllers\ArtifactsController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\ModulesController;
use App\Http\Controllers\ProjectsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/roles', [AuthController::class, 'roles'])->middleware('auth:sanctum');
//Users Route
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/register', [AuthController::class, 'register'])->middleware('auth:sanctum')->name('register');
    Route::get('/users', [AuthController::class, 'users'])->name('user');
    Route::delete('/users/{user}', [AuthController::class, 'deleteUser']);
});
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
    Route::post('/domains/massive', [DomainController::class, 'massiveStore']);
    Route::put('/domains/massive', [DomainController::class, 'massiveUpdate']);
    Route::put('/domains/{domain}', [DomainController::class, 'update']);
    Route::delete('/domains/massive', [DomainController::class, 'massiveDestroy']);
    Route::delete('/domains/{domain}', [DomainController::class, 'destroy']);
    });

//modules routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/modules', [ModulesController::class, 'index']);
    Route::get('/modules/{modules}', [ModulesController::class, 'show']);
    Route::post('/modules', [ModulesController::class, 'store']);
    Route::post('/modules/massive', [ModulesController::class, 'massiveStore']);
    Route::put('/modules/massive', [ModulesController::class, 'massiveUpdate']);
    Route::put('/modules/{modules}', [ModulesController::class, 'update']);
    Route::delete('/modules/massive', [ModulesController::class, 'massiveDestroy']);
    Route::delete('/modules/{modules}', [ModulesController::class, 'destroy']);
});

//audit trails routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/audit-trails', [AuditTrailController::class, 'index']);
});
