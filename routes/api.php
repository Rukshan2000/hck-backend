<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



// Authentication routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('permissions', [AuthController::class, 'permissions']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    
    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Task routes using controller group pattern
    Route::controller(TaskController::class)->group(function () {
        Route::get('/tasks', 'index');         // Get all tasks
        Route::post('/tasks', 'store');        // Add a new task
        Route::get('/tasks/{id}', 'show');     // View single task
        Route::put('/tasks/{id}', 'update');   // Update task
        Route::delete('/tasks/{id}', 'destroy'); // Delete task
    });
    
    // Additional routes for soft delete operations
    Route::prefix('roles')->group(function () {
        Route::get('trashed', [RoleController::class, 'trashed']);
        Route::patch('{id}/restore', [RoleController::class, 'restore']);
        Route::delete('{id}/force-delete', [RoleController::class, 'forceDelete']);
    });

    Route::prefix('menus')->group(function () {
        Route::get('trashed', [MenuController::class, 'trashed']);
        Route::patch('{id}/restore', [MenuController::class, 'restore']);
        Route::delete('{id}/force-delete', [MenuController::class, 'forceDelete']);
        Route::post('reorder', [MenuController::class, 'reorder']);
    });

    Route::prefix('users')->group(function () {
        Route::get('trashed', [UserController::class, 'trashed']);
        Route::patch('{id}/restore', [UserController::class, 'restore']);
        Route::delete('{id}/force-delete', [UserController::class, 'forceDelete']);
        Route::get('created-tasks', [TaskController::class, 'createdTasks']);
        Route::get('statistics', [TaskController::class, 'statistics']);
    });
    
    // Moving public API resources inside auth middleware
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('menus', MenuController::class);
    Route::apiResource('users', UserController::class);

    // Log debugging routes (protected by auth middleware)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/logs/view', [App\Http\Controllers\UserController::class, 'viewLogs']);
        Route::post('/logs/clear', [App\Http\Controllers\UserController::class, 'clearLogs']);
        Route::get('/logs/test', [App\Http\Controllers\UserController::class, 'testLogging']);
    });
});

// Mail testing routes
Route::prefix('mail')->group(function () {
    Route::get('/check-config', [App\Http\Controllers\MailTestController::class, 'checkMailConfig']);
    Route::post('/send-test', [App\Http\Controllers\MailTestController::class, 'sendTestMail']);
});

// Public routes (move inside auth middleware when implementing proper authentication)
// Route::apiResource('roles', RoleController::class);
// Route::apiResource('menus', MenuController::class);
// Route::apiResource('users', UserController::class);

// When you want to protect all routes with authentication, move the resource routes inside the auth middleware group
