<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::with(['users', 'menus']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Include trashed records if requested
        if ($request->has('with_trashed') && $request->with_trashed) {
            $query->withTrashed();
        }

        $roles = $query->paginate($request->get('per_page', 15));

        return response()->json($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'description' => 'nullable|string',
            'menu_ids' => 'sometimes|array',
            'menu_ids.*' => 'exists:menus,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Attach menus if provided
        if (isset($validated['menu_ids'])) {
            $role->menus()->sync($validated['menu_ids']);
        }

        $role->load(['menus', 'users']);

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load(['users', 'menus', 'roleMenus']);
        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('roles')->ignore($role->id)],
            'description' => 'sometimes|nullable|string',
            'menu_ids' => 'sometimes|array',
            'menu_ids.*' => 'exists:menus,id',
        ]);

        $role->update([
            'name' => $validated['name'] ?? $role->name,
            'description' => $validated['description'] ?? $role->description,
        ]);

        // Update menu associations if provided
        if (isset($validated['menu_ids'])) {
            $role->menus()->sync($validated['menu_ids']);
        }

        $role->load(['menus', 'users']);

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role
        ]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Role $role)
    {
        // Check if role has users assigned
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete role that has users assigned to it'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * Restore a soft deleted role.
     */
    public function restore($id)
    {
        $role = Role::withTrashed()->findOrFail($id);
        $role->restore();

        return response()->json([
            'message' => 'Role restored successfully',
            'role' => $role
        ]);
    }

    /**
     * Permanently delete a role.
     */
    public function forceDelete($id)
    {
        $role = Role::withTrashed()->findOrFail($id);
        
        // Check if role has users assigned (even in trashed state)
        if ($role->users()->withTrashed()->count() > 0) {
            return response()->json([
                'message' => 'Cannot permanently delete role that has users assigned to it'
            ], 422);
        }

        $role->forceDelete();

        return response()->json([
            'message' => 'Role permanently deleted'
        ]);
    }

    /**
     * Get trashed roles.
     */
    public function trashed(Request $request)
    {
        $query = Role::onlyTrashed()->with(['users', 'menus']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        $roles = $query->paginate($request->get('per_page', 15));

        return response()->json($roles);
    }
}
