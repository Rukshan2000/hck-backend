<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $tasks = Task::with(['creator', 'assignee'])->get();

            return response()->json([
                'status' => true,
                'tasks' => $tasks
            ], 200);
        } catch (Exception $e) {
            // Log error
            \Log::error('Error fetching tasks: ', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch tasks.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'deadline' => 'nullable|date|after_or_equal:today',
                'status' => 'nullable|in:' . implode(',', Task::getStatuses()),
                'assigned_to' => 'nullable|exists:users,id',
                'created_by' => 'nullable|exists:users,id',
            ]);

            // Set created_by to authenticated user if not provided
            $validated['created_by'] = $validated['created_by'] ?? Auth::id();
            
            // Set default status if not provided
            $validated['status'] = $validated['status'] ?? Task::STATUS_PENDING;

            $task = Task::create($validated);

            return response()->json([
                'message' => 'Task successfully created.',
                'task' => $task
            ], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $task = Task::with(['creator', 'assignee'])->find($id);
            
            if (!$task) {
                return response()->json([
                    'message' => 'Task not found.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'task' => $task
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $task = Task::find($id);
            if (!$task) {
                return response()->json([
                    'message' => 'Task not found.',
                ], 404);
            }

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'deadline' => 'nullable|date|after_or_equal:today',
                'status' => 'nullable|in:' . implode(',', Task::getStatuses()),
                'assigned_to' => 'nullable|exists:users,id',
            ]);

            // Update completed_at if status is changing to completed
            if (isset($validated['status']) && $validated['status'] === Task::STATUS_COMPLETED && $task->status !== Task::STATUS_COMPLETED) {
                $validated['completed_at'] = now();
            }

            $task->update($validated);

            return response()->json([
                'message' => 'Task successfully updated.',
                'task' => $task
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $task = Task::find($id);
            if (!$task) {
                return response()->json([
                    'message' => 'Task not found.',
                ], 404);
            }

            $task->delete();

            return response()->json([
                'message' => 'Task successfully deleted.',
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
