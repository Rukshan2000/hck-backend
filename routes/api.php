<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LessonController;

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
Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('me', [UserController::class, 'me']);
    Route::get('permissions', [UserController::class, 'permissions']);
    Route::post('update-profile', [UserController::class, 'updateProfile']);
    // Add other protected routes as needed
});

    // Task routes using controller group pattern
    Route::controller(TaskController::class)->group(function () {
        Route::get('/tasks', 'index');         // Get all tasks
        Route::post('/tasks', 'store');        // Add a new task
        Route::get('/tasks/{id}', 'show');     // View single task
        Route::put('/tasks/{id}', 'update');   // Update task
        Route::delete('/tasks/{id}', 'destroy'); // Delete task
    });
    
    // Lesson routes using controller group pattern
    Route::controller(LessonController::class)->group(function () {
        Route::get('/lessons', 'index');          // Get all lessons
        Route::post('/lessons', 'store');         // Add a new lesson
        Route::get('/lessons/{id}', 'show');      // View single lesson
        Route::put('/lessons/{id}', 'update');    // Update lesson
        Route::delete('/lessons/{id}', 'destroy'); // Delete lesson
    });
    
    // Additional routes for soft delete operations
    Route::prefix('roles')->group(function () {
        Route::get('trashed', [RoleController::class, 'trashed']);
        Route::patch('{id}/restore', [RoleController::class, 'restore']);
        Route::delete('{id}/force-delete', [RoleController::class, 'forceDelete']);
    });

    Route::prefix('lessons')->group(function () {
        Route::get('trashed', [LessonController::class, 'trashed']);
        Route::patch('{id}/restore', [LessonController::class, 'restore']);
        Route::delete('{id}/force-delete', [LessonController::class, 'forceDelete']);
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

    Route::get('/hi',function(){
        return "Hello";
    });
// Mail testing routes
//Route::prefix('mail')->group(function () {
  //  Route::get('/check-config', [App\Http\Controllers\MailTestController::class, 'checkMailConfig']);
   // Route::post('/send-test', [App\Http\Controllers\MailTestController::class, 'sendTestMail']);
//});

// Public routes (move inside auth middleware when implementing proper authentication)
// Route::apiResource('roles', RoleController::class);
// Route::apiResource('menus', MenuController::class);
// Route::apiResource('users', UserController::class);
// When you want to protect all routes with authentication, move the resource routes inside the auth middleware group
