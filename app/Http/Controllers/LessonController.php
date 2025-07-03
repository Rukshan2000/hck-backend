<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $lessons = Lesson::with('lecturer')->get();

            return response()->json([
                'status' => true,
                'lessons' => $lessons
            ], 200);
        } catch (Exception $e) {
            // Log error
            \Log::error('Error fetching lessons: ', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch lessons.',
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
                'attachment' => 'nullable|string',
                'created_by' => 'nullable|exists:lecturers,id',
            ]);

            // Set created_by to authenticated user if not provided
            $validated['created_by'] = $validated['created_by'] ?? Auth::id();
            
            $lesson = Lesson::create($validated);

            return response()->json([
                'message' => 'Lesson successfully created.',
                'lesson' => $lesson
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
            $lesson = Lesson::with('lecturer')->find($id);
            
            if (!$lesson) {
                return response()->json([
                    'message' => 'Lesson not found.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'lesson' => $lesson
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
            $lesson = Lesson::find($id);
            if (!$lesson) {
                return response()->json([
                    'message' => 'Lesson not found.',
                ], 404);
            }

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'attachment' => 'nullable|string',
            ]);

            $lesson->update($validated);

            return response()->json([
                'message' => 'Lesson successfully updated.',
                'lesson' => $lesson
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
            $lesson = Lesson::find($id);
            if (!$lesson) {
                return response()->json([
                    'message' => 'Lesson not found.',
                ], 404);
            }

            $lesson->delete();

            return response()->json([
                'message' => 'Lesson successfully deleted.',
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a listing of the trashed resources.
     */
    public function trashed()
    {
        try {
            $lessons = Lesson::onlyTrashed()->with('lecturer')->get();

            return response()->json([
                'status' => true,
                'lessons' => $lessons
            ], 200);
        } catch (Exception $e) {
            // Log error
            \Log::error('Error fetching trashed lessons: ', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch trashed lessons.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore the specified resource.
     */
    public function restore(string $id)
    {
        try {
            $lesson = Lesson::onlyTrashed()->find($id);
            if (!$lesson) {
                return response()->json([
                    'message' => 'Trashed lesson not found.',
                ], 404);
            }

            $lesson->restore();

            return response()->json([
                'message' => 'Lesson successfully restored.',
                'lesson' => $lesson
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Permanently delete the specified resource.
     */
    public function forceDelete(string $id)
    {
        try {
            $lesson = Lesson::onlyTrashed()->find($id);
            if (!$lesson) {
                return response()->json([
                    'message' => 'Trashed lesson not found.',
                ], 404);
            }

            $lesson->forceDelete();

            return response()->json([
                'message' => 'Lesson permanently deleted.',
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
